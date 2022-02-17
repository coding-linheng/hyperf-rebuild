<?php

use app\Controller\HelloController;

return [
    ['GET','/hello/index',[HelloController::class,'index']],
    ['GET','/hello/hyperf',[HelloController::class,'hyperf']],
];