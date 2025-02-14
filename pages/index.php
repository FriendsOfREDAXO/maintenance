<?php

/**
 * This file is part of the maintenance package.
 *
 * @author (c) Friends Of REDAXO
 * @author <friendsof@redaxo.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$addon = rex_addon::get('maintenance');
echo rex_view::title($addon->i18n('title'));

// include rex_be_controller::getCurrentPageObject()->getSubPath();
rex_be_controller::includeCurrentPageSubPath();
