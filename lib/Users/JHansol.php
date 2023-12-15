<?php

namespace Users;

use App\Proxy\Map;
use App\Proxy\Player;
use App\User;
use App\UserAction;
use App\Proxy\Entity;

/**
 * 넓은 곳으로 도망가는 것이 살길이다.
 */
class JHansol extends User {
    const UP    = -1;
    const DOWN  = 1;
    const LEFT  = -2;
    const RIGHT = 2;

    private array $target = ['x' => -1, 'y' => -1];
    private ?int $prev_direction = null;

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return 'j-Hansol';
    }

    /**
     * 플레이어 스프라이트 크기를 적용한 X좌표를 리턴한다.
     * @param Player $player
     * @param int $x
     * @return int
     */
    static function X(Player $player, int $x) : int {
        return (int)($x / $player->width);
    }

    /**
     * 플레이어 스프라이트 크기를 적용한 Y좌표를 리턴한다.
     * @param Player $player
     * @param int $y
     * @return int
     */
    static function Y(Player $player, int $y) : int {
        return (int)($y / $player->height);
    }

    /**
     * 현재 위치에서 플레이어가 이동 가능한 방향을 배열로 리턴한다.
     * @param Player $player
     * @return array
     */
    private function _getMoveAbleDirection(Player $player) : array {
        $t = [];
        if($this->canPlayerMoveUp($player)) $t[] = self::UP;
        if($this->canPlayerMoveDown($player)) $t[] = self::DOWN;
        if($this->canPlayerMoveLeft($player)) $t[] = self::LEFT;
        if($this->canPlayerMoveRight($player)) $t[] = self::RIGHT;
        return $t;
    }

    /**
     * 타겟 엔티티와의 실자표를 이용하여 방향을 결정한다.
     * @param Player $player
     * @param Entity $entity
     * @return array
     */
    private function _getDirectionTargetReal(Player $player, Entity $entity) : array {
        $t = [];
        $dx = $entity->x - $player->x;
        $dy = $entity->y - $player->y;

        if($dx < 0) $t[] = self::LEFT;
        elseif($dx > 0) $t[] = self::RIGHT;
        if($dy < 0) $t[] = self::UP;
        elseif($dy > 0) $t[] = self::DOWN;
        return $t;
    }

    /**
     * 특정 좌표와의 거리를 구한다.
     * @param Player $player
     * @param int $x
     * @param int $y
     * @return array
     */
    private function _getDirectionTarget(Player $player, int $x, int $y) : array {
        $t = [];
        $px = self::X($player, $player->x);
        $py = self::Y($player, $player->y);
        $dx = $x - $px;
        $dy = $y - $py;
        // error_log("dx : {$dx}/{$px}/{$x}, dy : {$dy}/{$py}/{$y}");
        if($dx < 0) $t[] = self::LEFT;
        elseif($dx > 0) $t[] = self::RIGHT;
        if($dy < 0) $t[] = self::UP;
        elseif($dy > 0) $t[] = self::DOWN;
        return $t;
    }

    /**
     * 플레이어와 타겟까지 지정 단위 거리 안에 있는지 판별한다.
     * @param Player $player
     * @param Entity $entity
     * @param int $unit
     * @return bool
     */
    private function _isRangeIn(Player $player, Entity $entity, int $unit) : bool {
        $dx = abs(self::X($player, $entity->x) - self::X($player, $player->x));
        $dy = abs(self::Y($player, $entity->x) - self::Y($player, $player->x));
        $dt = sqrt(pow($dx, 2) + pow($dy, 2));
        $du = (int)($dt / sqrt(pow($player->width, 2) + pow($player->height, 2)));
        return $du <= $unit;
    }

    /**
     * 마법 회피 가능한 방향을 리턴한다.
     * @param Player $player
     * @param array $magics
     * @return array
     */
    private function _getAvoidMagicDirection(Player $player, array $magics) : array {
        $t = [];
        foreach($magics as $magic) {
            if($this->_isRangeIn($player, $magic, 4)) {
                $t2 = $this->_getDirectionTargetReal($player, $magic);
                foreach($t2 as $d) if(!in_array($d, $t2)) $t[] = $d;
            }
        }
        return array_diff([self::DOWN, self::UP, self::RIGHT, self::LEFT], $t);
    }

    /**
     * 가장 넓은 빈공간을 찾아 좌표를 반환한다.
     * @param Player $player
     * @param Map $map
     * @param array $plist
     * @return int[]
     */
    private function _getMaxSquarePos(Player $player, Map $map, array $plist) : array {
        $TMap = [];
        $cols = ceil($map->width / $player->width);
        $rows = ceil($map->height / $player->height);
        $squares = [];
        $prev = 0;

        $mx = $my = $ms = 0;
        for($i = 0 ; $i < $rows; $i++) {
            $tr = [];
            for($j = 0 ; $j < $cols; $j++) $tr[] = 1;
            $TMap[] = $tr;
        }

        foreach($plist as $p) {
            $x = self::X($player, $p->x);
            $y = self::Y($player, $p->y);
            $TMap[$y][$x] = 0;
        }

        for($i = 0 ; $i <= $cols; $i++) $squares[] = 0;

        foreach($TMap as $row_index => $row) {
            foreach($row as $col_index => $value) {
                $tcol_index = $col_index +1;
                $temp = $squares[$tcol_index];
                if($value == 1) {
                    $v = $squares[$tcol_index] = min($squares[$col_index], $squares[$tcol_index], $prev) + 1;
                    if($ms < $v) {
                        $mx = $col_index;
                        $my = $row_index;
                        $ms = $v;
                    }
                }
                else $squares[$tcol_index] = 0;
                $prev = $temp;
            }
        }

        if($ms > 0) return ['x' => (int)($mx - $ms / 2), 'y' => (int)($my - $ms / 2)];
        else return ['x' => -1, 'y' => -1];
    }

    /**
     * 플레이어로부터 지정 좌표까지의 거리를 구한다.
     * @param Player $player
     * @param int $x
     * @param int $y
     * @return float
     */
    private function _getDistance(Player $player, int $x, int $y) : int {
        return (int)sqrt(pow(abs(self::X($player,$player->x) - $x), 2) +
            pow(abs(self::Y($player,$player->y) - $y), 2));
    }

    /**
     * @inheritDoc
     */
    public function action(Map $map, Player $own_player, array $other_player_list, array $magic_list,): UserAction {
        $pad = $this->_getMoveAbleDirection($own_player);
        $np = self::findNearestEntity($own_player, $other_player_list);
        $amd = $this->_getAvoidMagicDirection($own_player, $magic_list);

        $npd = $this->_getDistance($own_player, self::X($own_player, $np->x), self::Y($own_player,$np->y));
        $tdr = $this->_getDistance($own_player, $this->target['x'], $this->target['y']);
        if(($this->target['x'] == -1 && $this->target['y'] == -1) ||
            ($tdr <= $npd)) {
            $this->target = $this->_getMaxSquarePos($own_player, $map, $other_player_list);
        }

        if(self::calculateEntityDistance($own_player, $np) <= $own_player->half_width) return UserAction::MAGIC;
        else {
            $td = $this->_getDirectionTarget($own_player, $this->target['x'], $this->target['y']);
            $direction = array_intersect($td, $pad);
            $direction = array_intersect($direction, $amd);
            $direction = !empty($direction) ? reset($direction) : null;
            if(!$direction & in_array($this->prev_direction, $pad)) $direction = $this->prev_direction;
            elseif(!$direction)$direction = random_int(0, count($pad) > 0 ? count($pad) - 1 : 0);
            $this->prev_direction = $direction;
            // error_log("Direction : {$direction}");
            return match ($direction) {
                self::UP => UserAction::UP,
                self::DOWN => UserAction::DOWN,
                self::LEFT => UserAction::LEFT,
                self::RIGHT => UserAction::RIGHT,
                default => UserAction::STOP
            };
        }
    }
}