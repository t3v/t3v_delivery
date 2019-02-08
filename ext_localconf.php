<?php
defined('TYPO3_MODE') or die();

// === Variables ===

$extensionKey = $_EXTKEY;

// === Frontend ===

if (TYPO3_MODE === 'FE') {
  // --- Hooks ---

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
    \T3v\T3vDelivery\Hooks\ContentPostProcOutputHook::class . '->searchAndReplaceContent';
}

// === Backend ===

if (TYPO3_MODE === 'BE') {
  // --- Extbase Command Controllers ---

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$extensionKey] = \T3v\T3vDelivery\Command\RsyncCommandController::class;
}
