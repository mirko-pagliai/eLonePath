<?php
declare(strict_types=1);

echo __('Welcome back');

$dynamicMessage = 'not a literal, must be skipped';
echo __($dynamicMessage);
