<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">
        <link rel="icon" type="image/svg" href="../martindenis/favicon.svg" />
        <link rel="stylesheet" type="text/css" href="./index.css" media="screen" />
        <title>Snake</title>
    </head>
    <body>
        <div id="canvas-container">
            <canvas id="canvas" width="400" height="400"></canvas>
        </div>

        <div id="menu" class="menu active">
            <p class="menu__title" data-menu-type="main">Snake</p>
            <p class="menu__title hidden" data-menu-type="lost">Game over</p>
            <p class="menu__title hidden" data-menu-type="won">Congrats</p>
            <p class="menu__title hidden" data-menu-type="pause">Pause</p>
            <p class="menu__title hidden" data-menu-type="controls">Controls</p>

            <div id="menu__controls" data-menu-type="controls" class="hidden">
                <p>Use your <kbd>Arrow Keys</kbd> or <kbd>Swipe</kbd> in the desired direction to move the snake.</p>
                <p>Up to 2 direction changes can be registered in close succession but only one per tick will be executed.</p>
            </div>

            <form id="menu__form" method="post">
                <div data-menu-type="main">
                    <p>Choose a difficulty</p>
                    <ul id="choose__difficulty">
                        <li><input type="radio" name="choose__difficulty" id="easy" value="6_8_325"><label for="easy">Easy</label></li>
                        <li><input type="radio" name="choose__difficulty" id="medium" value="8_10_275" checked><label for="medium">Normal</label></li>
                        <li><input type="radio" name="choose__difficulty" id="difficult" value="11_14_225"><label for="difficult">Hard</label></li>
                    </ul>
                </div>

                <ul class="menu__buttons">
                    <li data-menu-type="main"><button type="submit" class="menu__play btn btn__primary">Play</button></li>
                    <li data-menu-type="won lost" class="hidden"><button type="submit" class="menu__play btn btn__primary">Replay</button></li>
                    <li data-menu-type="pause" class="hidden"><button type="button" class="menu__resume btn btn__primary">Resume</button></li>
                    <li data-menu-type="pause main"><button type="button" class="menu__controls btn btn__secondary">Controls</button></li>
                    <li data-menu-type="controls" data-state-type="pause" class="hidden"><button type="button" class="menu__pause btn btn__primary">Back</button></li>
                    <li data-menu-type="pause grid won lost controls" class="hidden"><button type="button" class="menu__main btn btn__secondary">Main menu</button></li>
                </ul>
            </form>
        </div>

        <a href="#" id="pause-button" class="menu__pause hidden" data-menu-type="off">||</a>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script type="module" src="./index.js"></script>
    </body>
</html>
