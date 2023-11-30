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
  menu: 'main', // off, main, pause, won, lost, controls
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
    this.state = 'waiting';
    this.direction = [];
    this.resize();

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
    this.updateMenu('off');

    this.drawGame();
  },

  prepareCanvas: function () {
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
        this.drawCell(this.candy.x, this.candy.y, this.colors.candy, this.candySize);
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

  drawGame: function () {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    this.drawCell(this.candy.x, this.candy.y, this.colors.candy, this.candySize);

    for (let s = 0; s < snake.length; s++) {
      const bodypart = snake[s];
      
      this.drawCell(bodypart.x, bodypart.y, (s == 0 ? this.colors.head : this.colors.body));
    }
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

    this.prepareCanvas();

    if (this.state != 'playing' && this.state != 'waiting')
      return;

    this.drawGame();
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
    const self = this;
    const $elements = $('[data-menu-type]');

    self.menu = type;
    $elements.hide();
    $elements.filter((i, el) => {
      let result = $(el).data('menu-type').split(' ').includes(type);

      if (result && $(el).data('state-type')) {
        return $(el).data('state-type').split(' ').includes(self.state)
      }

      return result;
    }).show();


    if (type == 'off') {
      $('#menu').removeClass('active');
    } else {
      if (titleText !== undefined) {
        $('.menu__title').text(titleText);
      }

      if (btnText !== undefined) {
        $('.menu__play').text(btnText);
      }

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
  game.updateMenu('off').play();
});

$('.menu__main').on('click', function (e) {
  e.preventDefault();
  game.reset();
});

$('.menu__controls').on('click', function (e) {
  e.preventDefault();
  game.updateMenu('controls')
});

$('.menu__input').on('focus', function(e) {
  $(this).select();
});

$('.menu__pause').on('click', function(e) {
  e.preventDefault();
  game.updateMenu('pause').pause();
});

$('#menu__form').on('submit', function(e) {
  e.preventDefault();
  game.init();
  return false;
});

window.addEventListener('resize', throttle(game.resize.bind(game), 200));

window.addEventListener('keydown', function (e) {
  if (game.menu == 'pause') {
    switch (e.code) {
      case 'Space':
      case 'Escape':
        game.updateMenu('off').play();
        break;
    }

    return;
  } else if (game.menu != 'off') {
    switch (e.code) {
      case 'Space':
        game.init();
        break;
      case 'Escape':
        game.reset();
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