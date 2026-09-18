<?php

try {
    doSomething();
} catch (Exception $e) {
    handleError($e);
}

?>
-----
<?php

try {
    doSomething();
} catch (\Throwable $e) {
    handleError($e);
}

?>
