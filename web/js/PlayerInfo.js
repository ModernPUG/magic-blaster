import * as PIXI from '/node_modules/pixi.js/dist/pixi.min.mjs';

export class PlayerInfo extends PIXI.Container {
    #container;
    #doPlayer;
    #doUsername;
    #doDamage;

    constructor(
        entity,
    ) {
        super();

        {
            const graphics = new PIXI.Graphics();
            graphics.beginFill(0x808080, 0.5);
            graphics.drawRoundedRect(0, 0, 180, 60, 10);
            graphics.endFill();
            this.addChild(graphics);
        }

        {
            const texture = PIXI.Texture.from(`/assets/sprites${entity.sprite_path}`);
            const sprite = PIXI.TilingSprite.from(texture, {
                width: entity.sprite_tile_width,
                height: entity.sprite_tile_height,
            });
            sprite.scale.x = entity.width / entity.sprite_tile_width;
            sprite.scale.y = entity.height / entity.sprite_tile_height;
            sprite.x = 10;
            sprite.y = 10;

            this.addChild(sprite);
            this.#doPlayer = sprite;
        }

        {
            const text = new PIXI.Text('Player', {
                fontFamily: 'CookieRunBold',
                fontSize: 36,
                fill: 0x2D333D,
            });
            text.scale.x = 0.5;
            text.scale.y = 0.5;
            text.x = 50;
            text.y = 5;

            this.addChild(text);
            this.#doUsername = text;
        }

        {
            const text = new PIXI.Text('0', {
                fontFamily: 'CookieRunBold',
                fontSize: 18,
                fill: 0x8B57C3,
            });
            text.x = 50;
            text.y = 30;

            this.addChild(text);
            this.#doDamage = text;
        }
    }

    updateData(entity) {
        this.#doPlayer.tilePosition.x = entity.sprite_tile_x
        this.#doPlayer.tilePosition.y = entity.sprite_tile_y;

        this.#doUsername.text = entity.username;
        this.#doDamage.text = entity.damage;
    }
}
