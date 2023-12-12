<?php

declare( strict_types=1 );

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
class Bonobono extends User {
    private const MODE_ATTACK = 1;
    private const MODE_RUN = 2;

    private int $mode = self::MODE_ATTACK;

    private ?int $target_player_id = null;

    /**
     * 게임 화면에 표시될 플레이어 이름입니다.
     *
     * @return string
     */
    public function getName(): string {
        return '보노보노';
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
        Map    $map,
        Player $own_player,
        array  $other_player_list,
        array  $magic_list,
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
        if ( $this->mode === self::MODE_ATTACK ) {
            return UserAction::LEFT;
        }
        if ( $this->mode === self::MODE_RUN ) {
            return UserAction::DOWN;
        }

        return UserAction::STOP;
    }
}
