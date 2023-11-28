# 2023년 모던 PHP 유저 그룹 송년회 게임

## 게임 안내

각자 자신의 플레이어 클래스를 개발하여 참여하는 게임입니다.
각 플레이어 클래스 알고리즘에 따라 플레이어는 이동하거나 마법을 사용할 수 있습니다.
마법은 일정 시간 후 발동하며 중심을 포함 상하좌우로 피해를 입힙니다.
가장 피해가 적은 플레이어가 승리합니다.

아래 스크린샷을 클릭하여 영상으로 확인해보세요.
[![2023년 MPUG 송년회 게임 트레일러](https://img.youtube.com/vi/HdUUz3zSOQQ/maxresdefault.jpg)](https://youtu.be/HdUUz3zSOQQ)

## 참여 방법

1. 이 저장소를 Fork 하여 내 저장소를 만듭니다.
2. lib/Users 디렉토리에 __{MyUniqueName}.php__ 로 클래스 파일을 만듭니다.
3. lib/Users/SampleUser.php 파일의 예제 코드를 참고하여 나만의 코드를 작성합니다.
4. 내 저장소에 커밋 후 __event 브랜치__ 로 보내는 Pull Request를 생성합니다.
5. MPUG 운영진이 코드를 검토 후 병합합니다.
6. 경품 당첨 시 연락을 받기 위한 [구글 설문지](https://forms.gle/2xmbc31uiTVNZmas8)를 작성합니다.

## 실행 방법

npm(또는 yarn, bun)과 docker-compose가 필요합니다.
터미널에서 아래의 과정을 진행합니다.

1. JS 패키지 설치

    ```shell
    npm install
    ```

    또는

    ```shell
    yarn install
    ```

    또는

    ```shell
    bun install
    ```

2. PHP 패키지 설치

    ```shell
    docker-compose run php composer install
    ```

3. 도커 컨테이너 실행

    ```shell
    docker-compose up -d
    ```

4. 웹브라우저에서 http://localhost:8180/ 으로 접속합니다.

## 게임 동작 방식

- 그래픽 출력은 웹브라우저에서 JS로 구현되었습니다.

- 실제 게임이 처리되는 서버는 PHP로 구현되었습니다.

- 클라이언트와 게임 서버는 웹소켓으로 통신합니다.

- 서버에서 실시간으로 처리되는 결과를 클라이언트로 전송하여 화면에 출력합니다.

## 클래스 코딩 안내

- PhpStorm, VSCode 등의 PHP를 지원하는 IDE나 편집기를 사용하세요.

- lib/Users/SampleUser.php 파일내의 코드와 주석을 참고하여 클래스를 작성하세요.

- 사각형 충돌 판정이나 거리 측정을 위한 다양한 메서드가 준비되어 있습니다.

- 본인이 만든 클래스의 프로퍼티와 메서드는 자유롭게 구성해도 됩니다.

- action() 메서드 내에서 발생한 Exception은 무시되며 플레이어는 아무런 행동을 하지 않습니다.

- 게임에 영향을 주거나 이벤트의 목적을 벗어나는 코드는 허가되지 않습니다.

- 수정된 코드가 게임에 반영이 되기 위해서는 도커 컨테이너를 재시작해야 합니다.

    ```shell
    docker-compose restart php
    ```

## 사용된 기술

- php:8.2-alpine docker image
- [Ratchet](https://github.com/ratchetphp/Ratchet)
- [ReactPHP EventLoop](https://github.com/reactphp/event-loop)
- [PixiJS](https://pixijs.com/)
