<?php require_once '../../includes.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="icon" type="image/svg" href="<?php echo $favicon_path; ?>favicon.svg" />
  <link rel="stylesheet" type="text/css" href="<?php echo $css_path; ?>game.css" media="screen" />
  <title>Snake</title>
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">

  <style>
    :root {
      --menu-bg: #33223B;
    }

    * {
      -webkit-tap-highlight-color: transparent;
    }

    html,
    body,
    canvas {
      width: 100%;
      height: 100%;
    }

    html {
      font-size: 15px;
      line-height: 1.5;
      font-family: 'Bungee', cursive;
    }

    body {
      margin: 0;
      padding: 12px;
      overflow: hidden;
      background-color: #211E2B;
    }

    #canvas-container {
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    #canvas {
      z-index: 10;
      width: 280px;
      height: 280px;

      box-shadow: 0 0 0 3px var(--menu-bg);
    }

    #canvas__joystick {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 20;
    }

    .menu {
      display: none;
      position: absolute;
      top: 50%;
      left: 50%;
      width: 240px;
      transform: translate(-50%, -50%);
      padding: 20px;
      background: var(--menu-bg);
      color: white;
      z-index: 200;
    }

    .menu.active {
      display: block;
    }

    .menu__buttons {
      margin: 0;
      padding: 0;
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .menu__title {
      margin: 0 0 20px;
      text-align: center;
      font-size: 1.5rem;
      line-height: 1.5;
    }

    .btn {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 10px 20px;
      border-radius: 0;
      width: 100%;
      border: none;
      line-height: 24px;
      color: white;
      font-family: inherit;
      cursor: pointer;
      outline: none;
    }

    .btn__primary {
      background: rgb(237, 143, 91);
      background: linear-gradient(90deg, rgba(237, 143, 91, 1) 0%, rgba(227, 109, 96, 1) 100%);
    }

    .btn__secondary {
      background: #9C4368;
      background: linear-gradient(90deg, #9C4368 0%, #6b354f 100%);
    }

    #choose__difficulty {
      padding: 0;
      list-style: none;
      margin-bottom: 15px;
    }
    #choose__difficulty li {
      display: flex;
      align-items: center;
    }

    #choose__difficulty label {
      padding: 4px 0 4px 10px;
      cursor: pointer;
      color: #d1b0c5;

      transition: color 0.25s ease-out;
    }

    #choose__difficulty input {
      background: none;
      border: 2px solid #9C4368;
      height: 20px;
      width: 20px;
      margin: 0;

      color: white;
      padding: 0;
      box-shadow: none;
      outline: none;
      font-size: 16px;
      border-radius: 0;
      line-height: 20px;
      -moz-appearance: none;
      appearance: none;
      cursor: pointer;

      transition: background 0.25s ease-out, border-color 0.25s ease-out, box-shadow 0.25s ease-out;
    }

    #choose__difficulty input:focus {
      border-color: #ed8f5b;
    }

    #choose__difficulty input:checked {
      background: #ed8f5b;
      border-color: #ed8f5b;
      box-shadow: inset 0 0 0 2px var(--menu-bg);
    }

    #choose__difficulty input:checked + label {
      color: #ffffff;
    }

    .hidden {
      display: none;
    }
  </style>
</head>

<body id="Snake">
  <a href="<?php echo $sandbox_path; ?>" id="back">Back</a>

  <div id="canvas-container">
    <canvas id="canvas" width="400" height="400"></canvas>
  </div>

  <div id="menu" class="menu active">
    <p class="menu__title" data-menu-type="main">Snake</p>
    <p class="menu__title hidden" data-menu-type="lost">Game over</p>
    <p class="menu__title hidden" data-menu-type="won">Congrats</p>
    <p class="menu__title hidden" data-menu-type="pause">Pause</p>

    <form id="menu__form" method="post">
      <div data-menu-type="main">
        <p>Choose a difficulty</p>
        <ul id="choose__difficulty">
          <li><input type="radio" name="choose__difficulty" id="easy" value="8_6_350"><label for="easy">6 x 8 - Slow</label></li>
          <li><input type="radio" name="choose__difficulty" id="medium" value="10_8_275" checked><label for="medium">8 x 11 - Medium</label></li>
          <li><input type="radio" name="choose__difficulty" id="difficult" value="15_11_200"><label for="difficult">11 x 14 - Fast</label></li>
        </ul>
      </div>

      <ul class="menu__buttons">
        <li data-menu-type="main grid"><button type="submit" class="menu__play btn btn__primary">Play</button></li>
        <li data-menu-type="won lost" class="hidden"><button type="submit" class="menu__play btn btn__primary">Replay</button></li>
        <!-- <li data-menu-type="main"><button class="menu__grid btn btn__secondary">Custom grid size</button></li> -->
        <li data-menu-type="pause" class="hidden"><button type="button" class="menu__resume btn btn__primary">Resume</button></li>
        <li data-menu-type="pause grid won lost" class="hidden"><button type="button" class="menu__main btn btn__secondary">Main menu</button></li>
      </ul>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="<?php echo $js_path; ?>tools<?php echo ($is_prod ? '.min' : '') ; ?>.js"></script>
  <script>
    // Variables
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const touchDevice = "ontouchstart" in document.documentElement;

    const MAX_CELLSIZE = 24;
    const MAX_DIRECTIONS = 2;
    Set.prototype.getByIndex = function (index) { return [...this][index]; }

    let snake = []

    var game = {
      timer: null,
      speed: 0,  // initialized later
      cols: 0, // initialized later
      rows: 0, // initialized later
      totalCells: 0, // initialized later
      cellSize: MAX_CELLSIZE,
      cellMargin: 3,
      freeCells: new Set(),
      joystick: null,
      animationFrame: null,
      candySize: 6,
      candy: {
        x: 0,
        y: 0,
      },
      start: {
        x: 1,
        y: 1
      },
      state: 'off', // off, pause, playing, waiting
      menu: 'main', // off, main, pause, won, lost
      colors: {
        candy: '#9C4368',
        head: '#ED8F5B',
        body: '#9C4368',
        won: '#437f9c',
        lost: '#D96459',
        background: '#211E2B',
      },
      directions: [],

      init: function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        this.resize();

        this.drawCell(this.start.x, this.start.y, this.colors.head);

        if (touchDevice) {
          this.joystick = new Joystick({
            baseBackgroundColor: '#33223b',
            baseBorderColor: '#33223b',
            stickBackgroundColor: '#33223b',
            stickBorderColor: '#ED8F5B',
            static: false,
            adaptivePositioning: true,
            onMove: function(dir, dist) {
              if (game.state == 'waiting') {
                game.play();
              }
              if (game.state == 'playing') {
                game.changeDirection(dir);
              }
            }
          }).draw();
        }

        this.updateMenu('off');
        this.state = 'waiting';
        this.direction = [];

        snake = [{
          x: this.start.x,
          y: this.start.y,
          direction: '',
        }]

        this.freeCells.clear();
        for (let c = 0; c < this.cols; c++) {
          for (let r = 0; r < this.rows; r++) {
            this.freeCells.add(c + '-' + r);
          }
        }

        this.freeCells.delete(this.start.x + '-' + this.start.y);

        this.placeCandy();
      },

      prepareCanvases: function () {
        const gameRatio = this.cols / this.rows;
        const windowRatio = $('#canvas-container').width() / $('#canvas-container').height();

        if (gameRatio < windowRatio) {
          this.cellSize = Math.floor(Math.min($('#canvas-container').height() - (this.cellMargin * (this.rows + 1))) / this.rows);
        } else {
          this.cellSize = Math.floor(Math.min($('#canvas-container').width() - (this.cellMargin * (this.cols + 1))) / this.cols);
        }

        this.cellSize = Math.min(this.cellSize, MAX_CELLSIZE);

        let pxWidth = this.cols * this.cellSize + (this.cellMargin * (this.cols + 1));
        let pxHeight = this.rows * this.cellSize + (this.cellMargin * (this.rows + 1));

        $(canvas).width(pxWidth).height(pxHeight);
        canvas.width = dpi(pxWidth);
        canvas.height = dpi(pxHeight);
      },

      nextStep: function () {
        let head = {
          x: snake[0].x,
          y: snake[0].y,
          direction: this.directions[0] ? this.directions[0] : snake[0].direction
        }

        switch (head.direction) {
          case 'left':
            head.x -= 1;
            break;
          case 'right':
            head.x += 1;
            break;
          case 'up':
            head.y -= 1;
            break;
          case 'down':
            head.y += 1;
            break;
        }

        if (this.directions.length > 0) {
          this.directions.shift();
        }

        // Game over if head goes over the border
        if (head.x < 0 || head.x >= this.cols || head.y < 0 || head.y >= this.rows) {
          this.lost();
          return;
        }

        // We erase the tail then remove it from the body.
        if (head.x != this.candy.x || head.y != this.candy.y) {
          this.drawCell(snake[snake.length - 1].x, snake[snake.length - 1].y, this.colors.background, this.cellSize + 2);
          this.freeCells.add(snake[snake.length - 1].x + '-' + snake[snake.length - 1].y);
          snake.pop();
        }

        // Game over if head goes over the body
        if (!this.freeCells.has(head.x + '-' + head.y)) {
          this.lost();
          return;
        }

        // We add the head to the body and draw it
        snake.unshift(head);
        this.freeCells.delete(head.x + '-' + head.y);
        this.drawCell(head.x, head.y, this.colors.head);

        // Check if the head reached a candy
        if (head.x == this.candy.x && head.y == this.candy.y) {
          // Place another one or game is won
          if (this.freeCells.size > 0) {
            this.placeCandy();
          } else {
            this.won();
            return;
          }
        }

        if (snake.length > 1) {
          this.drawCell(snake[1].x, snake[1].y, this.colors.body);
        }
      },

      placeCandy: function () {
        const index = Math.floor(this.freeCells.size * Math.random());
        const cell = this.freeCells.getByIndex(index);

        const x = parseInt(cell.split('-')[0]);
        const y = parseInt(cell.split('-')[1]);

        this.candy = {
          x: x,
          y: y,
        }

        this.drawCell(x, y, this.colors.candy, this.candySize);
      },

      drawCell: function (x, y, color, size) {
        let pxX = game.cellMargin + (x * (game.cellSize + game.cellMargin));
        let pxY = game.cellMargin + (y * (game.cellSize + game.cellMargin));

        if (size === undefined) {
          size = game.cellSize;
        } else {
          pxX += (game.cellSize - size) / 2;
          pxY += (game.cellSize - size) / 2;
        }

        ctx.beginPath();
        ctx.rect(dpi(pxX), dpi(pxY), dpi(size), dpi(size));
        ctx.fillStyle = color;
        ctx.fill();
      },

      changeDirection: function (direction) {
        const directionCount = this.directions.length;
        const lastDirection = this.directions[directionCount - 1] ? this.directions[directionCount - 1] : snake[0].direction;

        if (this.directions.length > MAX_DIRECTIONS) {
          this.directions.shift();
        } else if (this.directions.length > MAX_DIRECTIONS - 1) {
          this.directions.pop();
        }

        if (direction == 'left' && lastDirection != 'left' && lastDirection != 'right')
          this.directions.push('left');
        if (direction == 'right' && lastDirection != 'right' && lastDirection != 'left')
          this.directions.push('right');
        if (direction == 'up' && lastDirection != 'up' && lastDirection != 'down')
          this.directions.push('up');
        if (direction == 'down' && lastDirection != 'down' && lastDirection != 'up')
          this.directions.push('down');
      },

      resize: function() {
        const difficultyValues = $('#choose__difficulty input:checked').val().split('_');
        
        this.cols = parseInt(difficultyValues[0]);
        this.rows = parseInt(difficultyValues[1]);
        this.speed = parseInt(difficultyValues[2]);
        this.totalCells = this.cols * this.rows;

        this.prepareCanvases();

        if (this.state == 'off')
          return;

        this.drawCell(this.candy.x, this.candy.y, this.colors.candy, this.candySize);

        for (let s = 0; s < snake.length; s++) {
          const bodypart = snake[s];
          
          this.drawCell(bodypart.x, bodypart.y, (s == 0 ? this.colors.head : this.colors.body));
        }
      },

      reset: function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        this.candy = {x: 0, y: 0};
        this.snake = [];

        clearTimeout(this.timer);
        this.updateMenu('main', 'Snake', 'Play');
        this.state = 'off';

        return this;
      },

      pause: function() {
        this.state = 'pause';
        clearTimeout(this.timer);
        
        return this;
      },

      play: function() {
        this.state = 'playing';
        this.animate();
        
        return this;
      },

      updateMenu: function(type, titleText, btnText) {
        this.menu = type;

        if (type == 'off') {
          $('#menu').removeClass('active');
        } else {
          const $elements = $('#menu').find('[data-menu-type]');

          if (titleText !== undefined)
            $('.menu__title').text(titleText);
          if (btnText !== undefined)
            $('.menu__play').text(btnText);

          $elements.hide();
          $elements.filter((i, el) => $(el).data('menu-type').split(' ').includes(type)).show();
          $('#menu').addClass('active');
        }

        return this;
      },

      lost: function () {
        this.pause();
        this.updateMenu('lost', 'Game Over', 'Replay');

        for (const cell of snake) {
          this.drawCell(cell.x, cell.y, game.colors.lost);
        }
      },

      won: function () {
        this.pause();
        this.updateMenu('won', 'Congrats', 'Replay');

        for (const cell of snake) {
          this.drawCell(cell.x, cell.y, game.colors.won);
        }
      },
      
      animate: function() {
        const self = this;

        self.timer = setTimeout(function() {
          self.animate();
        }, self.speed);

        if (self.state == 'playing') {
          self.nextStep();
        }        
      },
    }

    function Joystick(options) {
      const self = this;

      _hidden = true;
      _holding = false;
      _disabled = false;
      _distance = 0;
      _direction = false;
      _angle = 0; // radians
      _pos = {};
      _stickPos = {};
      _settings = {
        context: null,
        stickRadius: 25,
        baseRadius: 30,
        maxDistance: 20,
        adaptivePositioning: false,
        deadzone: 0.25, // 0 (center of joystick) to 1 (radius). Defines the value under which the joysticks doesn't fire.
        static: true, // If false, touchstart or mousedown will decide its position.
        baseBackgroundColor: '#000000',
        baseBorderColor: '#ffffff',
        stickBackgroundColor: '#000000',
        stickBorderColor: '#ffffff',
        initialPosition: { // Initial position on the canvas. 
          x: innerWidth - 80,
          y: innerHeight - 80
        },
        onMove: function() {},
        onStop: function() {}
      }

      self.cfg = {}

      _init = function() {
        self.cfg = {..._settings, ...options};

        if (self.cfg.static) {
          _hidden = false;
        }

        _pos = {...self.cfg.initialPosition};
        _stickPos = {..._pos};

        // Event listeners
        window.addEventListener('touchstart', event._onTouchstart);
        window.addEventListener('touchend', event._onTouchend);
        if (self.cfg.adaptivePositioning)
          window.addEventListener('touchmove', throttle(event._onTouchmove, 50));
        else
          window.addEventListener('touchmove', event._onTouchmove);
      }

      _getDirection = function() {
        if (_distance < self.cfg.deadzone * self.cfg.baseRadius) {
          return false;
        }

        let angle = _angle;

        if (angle < 0) {
          angle += Math.PI * 2
        }

        if (angle >= Math.PI * 0.25 && angle < Math.PI * 0.75) {
          return 'down';
        } else if (angle >= Math.PI * 0.75 && angle < Math.PI * 1.25) {
          return 'left';
        } else if (angle >= Math.PI * 1.25 && angle < Math.PI * 1.75) {
          return 'up';
        } else {
          return 'right';
        }
      }

      event = {
        _onTouchstart: function(e) {
          if (_disabled) return;

          const x = e.targetTouches[0].pageX;
          const y = e.targetTouches[0].pageY;

          if (self.cfg.static) {
            _distance = getDistance(_pos.x, _pos.y, x, y);
            if (_distance > self.cfg.baseRadius) {
              return;
            }
          } else {
            _hidden = false;
            
            self.move(x, y);
          }

          _holding = true;
          self.moveStick(x, y);
        },
        _onTouchmove: function(e) {
          if (_disabled || !_holding) return;

          const x = e.targetTouches[0].pageX;
          const y = e.targetTouches[0].pageY;
          const currentDirection = _getDirection();        

          self.moveStick(x, y);

          if (currentDirection != _direction) {
            if (currentDirection === false) {
              self.cfg.onStop();
            } else {
              self.cfg.onMove(currentDirection, _distance);
            }
          }

          if (self.cfg.adaptivePositioning) {
            self.move(x, y);
          }

          _direction = currentDirection;
        },
        _onTouchend: function(e) {
          if (_disabled || !_holding) return;

          if (!self.cfg.static) {
            _hidden = true;
          }

          _holding = false;
          self.moveStick(_pos.x, _pos.y);
          self.cfg.onStop();
        },
      }

      self.move = function(x, y) {
        _pos.x = x;
        _pos.y = y;
      },

      self.moveStick = function(x, y) {
        _distance = Math.min(self.cfg.maxDistance, getDistance(_pos.x, _pos.y, x, y));
        _angle = getAngleBetweenPoints(_pos, {x: x, y: y});

        _stickPos.x = _pos.x + Math.cos(_angle) * _distance;
        _stickPos.y = _pos.y + Math.sin(_angle) * _distance;

        self.draw();
      }

      self.draw = function() {
        if (self.cfg.context == null)
          return;
        
        self.cfg.context.clearRect(0, 0, dpi(innerWidth), dpi(innerHeight));

        if (_hidden)
          return;

        self.cfg.context.lineWidth = dpi(3);

        self.cfg.context.beginPath();
        self.cfg.context.arc(dpi(_pos.x), dpi(_pos.y), dpi(self.cfg.baseRadius), 0, 2 * Math.PI);
        self.cfg.context.fillStyle = self.cfg.baseBackgroundColor;
        self.cfg.context.strokeStyle = self.cfg.baseBorderColor;
        self.cfg.context.fill();
        self.cfg.context.stroke();
        self.cfg.context.closePath();

        self.cfg.context.beginPath();
        self.cfg.context.arc(dpi(_stickPos.x), dpi(_stickPos.y), dpi(self.cfg.stickRadius), 0, 2 * Math.PI);
        self.cfg.context.fillStyle = self.cfg.stickBackgroundColor;
        self.cfg.context.strokeStyle = self.cfg.stickBorderColor;
        self.cfg.context.fill();
        self.cfg.context.stroke();
        self.cfg.context.closePath();
      }

      self.enable = function() {
        _disabled = false;
      }

      self.disable = function() {
        _disabled = true;
        _hidden = true;
        if (self.cfg.context != null)
          self.cfg.context.clearRect(0, 0, dpi(innerWidth), dpi(innerHeight));
      }

      _init();

      return self;
    }

    // Event Listeners
    $('.menu__resume').on('click', function (e) {
      e.preventDefault();
      game.play();
    });

    $('.menu__main').on('click', function (e) {
      e.preventDefault();
      game.reset();
    });

    $('.menu__input').on('focus', function(e) {
      $(this).select();
    });

    $('#menu__form').on('submit', function(e) {
      e.preventDefault();
      game.init();
      return false;
    });

    // getDirection = function (x, y) {
    //   const valueX = 0.5 - (x / innerWidth);
    //   const valueY = 0.5 - (y / innerHeight);

    //   if (Math.abs(valueX) > Math.abs(valueY)) {
    //     // horizontal
    //     if (valueX > 0) {
    //       return 'left';
    //     } else {
    //       return 'right';
    //     }
    //   } else {
    //     // vertical
    //     if (valueY > 0) {
    //       return 'up';
    //     } else {
    //       return 'down';
    //     }
    //   }
    // }

    window.addEventListener('resize', throttle(game.resize.bind(game), 200));

    window.addEventListener('keydown', function (e) {
      if (game.menu == 'pause') {
        switch (e.code) {
          case 'Space':
          case 'Escape':
            game.play();
            break;
        }

        return;
      } else if (game.menu != 'off') {
        switch (e.code) {
          case 'Space':
            game.init();
            break;
          case 'Escape':
            game.updateMenu('main');
            break;
        }

        return;
      }

      switch (e.code) {
        case 'q':
        case 'ArrowLeft':
          game.changeDirection('left');
          break;
        case 'd':
        case 'ArrowRight':
          game.changeDirection('right');
          break;
        case 'z':
        case 'ArrowUp':
          game.changeDirection('up');
          break;
        case 's':
        case 'ArrowDown':
          game.changeDirection('down');
          break;
        case 'Escape':
          game.menu == 'off' ? game.updateMenu('pause').pause() : game.updateMenu('off').play();
          break;
      }

      if (game.state == 'waiting') {
        game.play();
      }
    });

    // window.addEventListener('touchstart', function (e) {
    //   if (game.menu != 'off') {
    //     return;
    //   } else if (game.state == 'waiting') {
    //     game.play();
    //   }

      // if (game.state == 'playing') {
      //   const x = e.touches[0].pageX;
      //   const y = e.touches[0].pageY;

      //   game.changeDirection(getDirection(x, y));
      // }
    // });
  </script>
</body>

</html>