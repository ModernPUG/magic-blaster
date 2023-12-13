<?php

declare(strict_types=1);

namespace Users;

use App\Rectangle;
use App\User;
use App\UserAction;
use App\Proxy\Map;
use App\Proxy\Entity;
use App\Proxy\Player;
use App\Proxy\Magic;

/**
 * 본인이 개발한 클래스에 대한 소개를 주석에 자유롭게 작성해주세요.
 * 이 예제 코드를 참고하여 본인만의 클래스를 만들어주세요.
 */

class ViramUser extends User
{
    private const MODE_ATTACK = 1;
    private const MODE_RUN = 2;

    private int $mode = self::MODE_ATTACK;

    private ?int $target_player_id = null;

    /**
     * 게임 화면에 표시될 플레이어 이름입니다.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'v1r4m';
    }

    /**
     * 사용자 액션
     *
     * @param \App\Proxy\Map $map 지도
     * @param \App\Proxy\Player $own_player 플레이어 본인
     * @param \App\Proxy\Player[] $other_player_list 다른 플레이어 목록
     * @param \App\Proxy\Magic[] $magic_list 마법 목록
     * @return \App\UserAction
     */
    public function action(
        Map $map,
        Player $own_player,
        array $other_player_list,
        array $magic_list,
    ): UserAction {
        /*
        \App\Proxy\Map 클래스는 지도의 정보를 제공합니다.

        플레이어(\App\Proxy\Player), 매직(\App\Proxy\Magic)은 엔터티(\App\Proxy\Entity)입니다.

        이 클래스는 \App\User 클래스를 상속받아 구현되었습니다.
        \App\User 클래스는 엔터티 간의 충돌 여부, 거리 등을 계산하는 메서드를 제공합니다.
        \App\User가 제공하는 정적 메서드, 인스턴스 메서드를 활용해 보세요.

        모든 엔터티는 사각형(\App\Rectangle)을 가지고 있습니다.
        getRectangle() 메서드를 통해 사각형을 가져올 수 있습니다.
        사각형을 이용하여 두 엔터티가 충돌하는지 여부를 확인할 수 있습니다.

        \App\Rectangle 클래스는 사각형 간의 충돌 여부, 거리 등을 계산하는 메서드를 제공합니다.
        \App\Rectangle 클래스가 제공하는 정적 메서드, 인스턴스 메서드를 활용해 보세요.
        */

        // 공격 모드
        if ($this->mode === self::MODE_ATTACK) {
            $target_player = null;

            // 공격 대상이 없다면
            if ($this->target_player_id === null) {
                // 거리가 가까운 순서대로 플레이어 목록을 정렬한다.
                /** @var Player[] */
                $other_player_list = self::sortEntityListByDistance(
                    $own_player,
                    $other_player_list,
                );

                // 피해가 가장 적은 플레이어를 찾는다.
                $min_damage = 999999;
                foreach ($other_player_list as $other_player) {
                    if ($other_player->damage < $min_damage) {
                        $target_player = $other_player;
                    }
                }

                $this->target_player_id = $target_player->id;
            // 공격 대상이 있다면
            } else {
                foreach ($other_player_list as $other_player) {
                    if ($other_player->id === $this->target_player_id) {
                        $target_player = $other_player;
                        break;
                    }
                }
            }

            $diff_x = $own_player->x - $target_player->x;
            $diff_y = $own_player->y - $target_player->y;

            $abs_diff_x = abs($diff_x);
            $abs_diff_y = abs($diff_y);

            // 거리가 가까우면
            if (
                (
                    $abs_diff_x <= $own_player->half_width
                    && $abs_diff_y <= $own_player->height
                )
                ||
                (
                    $abs_diff_y <= $own_player->half_height
                    && $abs_diff_x <= $own_player->width
                )
            ) {
                // 마법을 사용할 수 있다면
                if ($own_player->can_use_magic) {
                    // 도망 모드로 전환
                    $this->mode = self::MODE_RUN;
                    // 공격 대상 초기화
                    $this->target_player_id = null;

                    return UserAction::MAGIC;
                }
            }

            $user_action = null;

            // 수평 이동
            $fn_horizontal_move = function () use ($own_player, $diff_x) {
                $user_action = null;

                if ($diff_x > 0) {
                    if ($this->canPlayerMoveLeft($own_player)) {
                        $user_action = UserAction::LEFT;
                    }
                } elseif ($diff_x < 0) {
                    if ($this->canPlayerMoveRight($own_player)) {
                        $user_action = UserAction::RIGHT;
                    }
                }

                return $user_action;
            };

            // 수직 이동
            $fn_vertical_move = function () use ($own_player, $diff_y) {
                $user_action = null;

                if ($diff_y > 0) {
                    if ($this->canPlayerMoveUp($own_player)) {
                        $user_action = UserAction::UP;
                    }
                } elseif ($diff_y < 0) {
                    if ($this->canPlayerMoveDown($own_player)) {
                        $user_action = UserAction::DOWN;
                    }
                }

                return $user_action;
            };

            if (abs($diff_x) > abs($diff_y)) {
                $user_action = $fn_horizontal_move();
                // 수평 이동 실패 시 수직 이동
                if (!$user_action) {
                    $user_action = $fn_vertical_move();
                }
            } else {
                $user_action = $fn_vertical_move();
                // 수직 이동 실패 시 수평 이동
                if (!$user_action) {
                    $user_action = $fn_horizontal_move();
                }
            }

            if (!$user_action) {
                $user_action = UserAction::STOP;
            }

            return $user_action;
        }

        // 도망 모드
        if ($this->mode === self::MODE_RUN) {
            // 거리가 가까운 마법을 찾는다.
            /** @var Magic|null */
            $target_magic = self::findNearestEntity(
                $own_player,
                $magic_list,
            );

            if ($target_magic === null) {
                $this->mode = self::MODE_ATTACK;
                return UserAction::STOP;
            }

            // 플레이어가 지도의 중앙에서부터 얼마나 떨어져 있는지 계산
            $map_distance = $map->calculateEntityDistanceFromCenter($own_player);

            // 플레이어와 마법의 거리를 계산
            $magic_distance = self::calculateEntityDistance(
                $own_player,
                $target_magic,
            );

            // 플레이어와 마법의 거리가 어느 정도 멀어지면
            if (
                abs($magic_distance['horizontal']) >= $own_player->width * mt_rand(20, 30)
                && abs($magic_distance['vertical']) >= $own_player->height * mt_rand(20, 30)
            ) {
                $this->mode = self::MODE_ATTACK;
                return UserAction::STOP;
            }

            $magic_h_v_diff = abs($magic_distance['horizontal']) - abs($magic_distance['vertical']);

            // 마법과의 거리가 수평으로 더 가까우면
            if ($magic_h_v_diff < 0) {
                // 마법이 플레이어 왼쪽에 있으면
                if ($magic_distance['horizontal'] < 0) {
                    $user_action = UserAction::RIGHT;
                // 마법이 플레이어 오른쪽에 있으면
                } elseif ($magic_distance['horizontal'] > 0) {
                    $user_action = UserAction::LEFT;
                // 위치가 동일하면
                } else {
                    //여기에 중앙으로 가는 코드가 있었는데 제외
                    // // 플레이어가 지도의 중앙보다 오른쪽에 있으면
                    // if ($map_distance['horizontal'] < 0) {
                    //     $user_action = UserAction::LEFT;
                    // // 플레이어가 지도의 중앙보다 왼쪽에 있거나 같으면
                    // } else {
                    //     $user_action = UserAction::RIGHT;
                    // }
                }

                // 왼쪽으로 갈 수 있는지 확인
                if ($user_action === UserAction::LEFT) {
                    if (!$this->canPlayerMoveLeft($own_player)) {
                        $user_action = UserAction::RIGHT;
                    }
                // 오른쪽으로 갈 수 있는지 확인
                } else {
                    if (!$this->canPlayerMoveRight($own_player)) {
                        $user_action = UserAction::LEFT;
                    }
                }
            // 마법과의 거리가 수직으로 더 가깝거나 같으면
            } else {
                // 마법이 플레이어 위쪽에 있으면
                if ($magic_distance['vertical'] < 0) {
                    $user_action = UserAction::DOWN;
                // 마법이 플레이어 아래쪽에 있으면
                } elseif ($magic_distance['vertical'] > 0) {
                    $user_action = UserAction::UP;
                // 위치가 동일하면
                } else {
                    //d여기에 중앙으로 가는 코드가 있었는데 제외 
                    // // 플레이어가 지도의 중앙보다 위쪽에 있으면
                    // if ($map_distance['vertical'] < 0) {
                    //     $user_action = UserAction::DOWN;
                    // // 플레이어가 지도의 중앙보다 아래쪽에 있거나 같으면
                    // } else {
                    //     $user_action = UserAction::UP;
                    // }
                }

                // 위쪽으로 갈 수 있는지 확인
                if ($user_action === UserAction::UP) {
                    if (!$this->canPlayerMoveUp($own_player)) {
                        $user_action = UserAction::DOWN;
                    }
                // 아래쪽으로 갈 수 있는지 확인
                } else {
                    if (!$this->canPlayerMoveDown($own_player)) {
                        $user_action = UserAction::UP;
                    }
                }
            }

            return $user_action;
        }

        return UserAction::STOP;
    }
}
