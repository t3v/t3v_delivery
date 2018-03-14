<?php
defined('TYPO3_MODE') or die();

// === Variables ===

$extensionKey = $_EXTKEY;

// === Backend ===

if (TYPO3_MODE === 'BE') {
  // --- Extbase Command Controllers ---

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$extensionKey] = \T3v\T3vDelivery\Command\RsyncCommandController::class;
}

// === Frontend ===

if (TYPO3_MODE === 'FE') {
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
    \T3v\T3vDelivery\Hooks\ContentPostProcOutputHook::class . '->searchAndReplaceContent';
}