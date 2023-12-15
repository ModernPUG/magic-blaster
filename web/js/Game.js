import * as PIXI from '/node_modules/pixi.js/dist/pixi.min.mjs';
import {PlayerInfo} from '/js/PlayerInfo.js';

export class Game {
    #serverHost;
    #serverPort;
    #socket;

    #elScreen;

    #pixiApp;
    #spriteList = {};
    #infoStagePlayerList = {};

    #playStage;
    #infoStage;

    #soundBgm;

    constructor(
        serverHost,
        serverPort,
        screenSelector,
        dashboardSelector,
    ) {
        this.#serverHost = serverHost;
        this.#serverPort = serverPort;
        this.#elScreen = document.querySelector(screenSelector);

        this.#soundBgm = new Audio('/assets/sounds/battle.ogg');
        this.#soundBgm.loop = true;
        this.#soundBgm.volume = 0.3;
    }

    #connectSocket() {
        return new Promise((resolve, reject) => {
            if (this.#socket && this.#socket.readyState === WebSocket.OPEN) {
                resolve();
                return;
            }

            if (!this.#socket || this.#socket.readyState !== WebSocket.OPEN) {
                this.#socket?.close();
                this.#socket = new WebSocket(`ws://${this.#serverHost}:${this.#serverPort}`);
            }

            const socket = this.#socket;

            socket.onmessage = e => {
                const message = JSON.parse(e.data);

                switch (message.type) {
                    case 'init_game':
                        this.#initGame(message.data);
                        break;

                    case 'update_game':
                        this.#updateGame(message.data);
                        break;

                    case 'game_over':
                        this.#gameOver();
                        break;
                }
            };

            socket.onopen = e => {
                console.log('WebSocket opened:', e);
                resolve();
            };

            socket.onclose = e => {
                console.log('WebSocket closed:', e);
            };

            socket.onerror = e => {
                console.error('WebSocket error:', e);
                reject(e);
            };
        });
    }

    #sendSocketMessage(message) {
        this.#connectSocket().then(() => {
            this.#socket.send(message);
        });
    }

    #initGame(data) {
        this.#soundBgm.pause();
        this.#soundBgm.currentTime = 0;

        this.#spriteList = {};

        if (this.#pixiApp) {
            this.#pixiApp.destroy(true);
        }

        const playStageWidth = data.screen_width;
        const playStageHeight = data.screen_height;
        const infoStageWidth = 400;
        const resolution = window.devicePixelRatio;

        PIXI.settings.SCALE_MODE = PIXI.SCALE_MODES.NEAREST;

        const elCanvas = document.createElement('canvas');
        elCanvas.style.width = `${playStageWidth + infoStageWidth}px`;
        elCanvas.style.height = `${playStageHeight}px`;

        this.#pixiApp = new PIXI.Application({
            width: playStageWidth + infoStageWidth,
            height: playStageHeight,
            view: elCanvas,
            resolution: resolution,
        });

        // 플레이 스테이지
        {
            const playStage = new PIXI.Container();
            playStage.sortableChildren = true;
            playStage.position.set(0, 0);
            this.#pixiApp.stage.addChild(playStage);
            this.#playStage = playStage;

            // 배경 타일
            const texture = PIXI.Texture.from('/assets/sprites/bg_tile1.png');
            const tilingSprite = new PIXI.TilingSprite(
                texture,
                playStageWidth,
                playStageHeight,
            );
            this.#playStage.addChild(tilingSprite);
        }

        // 정보 스테이지
        {
            const infoStage = new PIXI.Container();
            infoStage.sortableChildren = true;
            infoStage.position.set(playStageWidth, 0);
            this.#pixiApp.stage.addChild(infoStage);
            this.#infoStage = infoStage;

            // 배경 타일
            const texture = PIXI.Texture.from('/assets/sprites/bg_tile2.png');
            const tilingSprite = new PIXI.TilingSprite(
                texture,
                infoStageWidth,
                playStageHeight,
            );
            this.#infoStage.addChild(tilingSprite);
        }

        this.#elScreen.innerHTML = '';
        this.#elScreen.appendChild(this.#pixiApp.view);

        this.#soundBgm.play();
    }

    #updateGame(data) {
        const unusedSpriteList = {...this.#spriteList};

        data.entity_list.forEach(entity => {
            let sprite = this.#spriteList[entity.id] ?? null;

            if (sprite) {
                delete unusedSpriteList[entity.id];
            } else {
                const texture = PIXI.Texture.from(`/assets/sprites${entity.sprite_path}`);

                sprite = PIXI.TilingSprite.from(texture, {
                    width: entity.sprite_tile_width,
                    height: entity.sprite_tile_height,
                });

                sprite.scale.x = entity.width / entity.sprite_tile_width;
                sprite.scale.y = entity.height / entity.sprite_tile_height;
                sprite.zIndex = entity.sprite_z_index;

                sprite.anchor.set(0.5);

                this.#spriteList[entity.id] = sprite;

                this.#playStage.addChild(sprite);
            }

            sprite.x = entity.x;
            sprite.y = entity.y;

            sprite.tilePosition.x = entity.sprite_tile_x
            sprite.tilePosition.y = entity.sprite_tile_y;

            if (entity.type === 'Player') {
                this.#updatePlayerInfo(entity);
            }

            if (entity.sound_effect) {
                const sound = new Audio(`/assets/sounds/${entity.sound_effect}`);
                sound.volume = 0.3;
                sound.play();
            }
        });

        // 사용하지 않은 스프라이트 삭제
        Object.keys(unusedSpriteList).forEach(id => {
            const sprite = this.#spriteList[id];
            this.#playStage.removeChild(sprite);
            delete this.#spriteList[id];
        });

        // 스테이지 렌더링
        this.#pixiApp.renderer.render(this.#pixiApp.stage);
    }

    #updatePlayerInfo(entity) {
        let playerInfo = this.#infoStagePlayerList[entity.id] ?? null;

        if (!playerInfo) {
            playerInfo = new PlayerInfo(entity);
            this.#infoStagePlayerList[entity.id] = playerInfo;

            const margin = 5;
            let playerInfoCount = 1; // 새로 추가할 것이니 1부터 시작
            for (const child of this.#infoStage.children) {
                if (child instanceof PlayerInfo) {
                    ++playerInfoCount;
                }
            }

            playerInfo.x = margin + ((playerInfoCount % 2 === 1) ? 0 : playerInfo.width + margin);
            playerInfo.y = (Math.round(playerInfoCount / 2) - 1) * (playerInfo.height + margin) + margin;

            this.#infoStage.addChild(playerInfo);
        }

        playerInfo.updateData(entity);
    }

    #gameOver() {
        this.#soundBgm.pause();
        alert('Game Over!');
    }

    newGame() {
        this.#sendSocketMessage('new_game');
    }
}