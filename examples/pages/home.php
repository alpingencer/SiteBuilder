<?php

global $sb;
$sb->getCurrentPage()->addComponent(ExampleComponent::newInstance('Hello World!', 5));
