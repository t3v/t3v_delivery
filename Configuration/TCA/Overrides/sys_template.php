<?php
defined('TYPO3_MODE') or die();

// === Variables ===

$extensionKey   = 't3v_delivery';
$extensionTitle = 'T3v Delivery';

// === TypoScript ===

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extensionKey, 'Configuration/TypoScript', $extensionTitle);
