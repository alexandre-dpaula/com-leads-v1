<?php

use App\Core\Csrf;
use App\Core\Helpers;
?>
<input type="hidden" name="_csrf" value="<?= Helpers::e(Csrf::token()) ?>">
