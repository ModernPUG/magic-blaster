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

class SongajiUser extends SampleUser
{
    private ?UserAction $user_action = UserAction::UP;
    public function getName(): string
    {
        return '송아지';
    }

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

        // // 왼쪽으로 갈수 있으면
        // if ($this->canPlayerMoveRight($own_player)) {
        //     return UserAction::RIGHT;
        // }

        if($this->user_action !== null ) {

            if($this->user_action == UserAction::UP && !$this->canPlayerMoveUp($own_player)) {
                $this->user_action = UserAction::LEFT;
            } else if($this->user_action == UserAction::LEFT && !$this->canPlayerMoveLeft($own_player)) {
                $this->user_action = UserAction::DOWN;
            } else if($this->user_action == UserAction::DOWN && !$this->canPlayerMoveDown($own_player)) {
                $this->user_action = UserAction::RIGHT;
            } else if($this->user_action == UserAction::RIGHT && !$this->canPlayerMoveRight($own_player)) {
                $this->user_action = UserAction::UP;
            }


            return $this->user_action;
        }
        
        return $this->user_action;
    }
}
