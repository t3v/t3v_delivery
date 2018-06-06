<?php
namespace T3v\T3vDelivery\Hooks;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * The content post proc output hook class.
 *
 * Searches and replaces in the generated output using regular expressions.
 *
 * @package T3v\T3vDelivery\Hooks
 */
class ContentPostProcOutputHook {
  /**
   * Searches for a string and replaces it with something else.
   *
   * You must set the search and replace patterns via TypoScript.
   *
   * TypoScript (setup) example:
   *
   * config {
   *   tx_t3vdelivery {
   *     search {
   *       1 = #(href|src)=("|')(/?)(fileadmin|typo3temp|uploads)/#
   *
   *       2 = lala
   *     }
   *
   *     replace {
   *       1 = $1=$2{$config.tx_t3vdelivery.schema}{$config.tx_t3vdelivery.host}/$4/
   *
   *       2 = Smörgåsbord
   *     }
   *   }
   * }
   *
   * @param array $parameters The parameters delivered by the caller (`tslib_fe`)
   * @param TypoScriptFrontendController $parentObject The parent object (`tslib_fe`)
   */
  public function searchAndReplaceContent(&$parameters, TypoScriptFrontendController $parentObject) {
    if (TYPO3_MODE === 'FE') {
      // Fetch the configuration
      $configuration = $parentObject->config['config']['tx_t3vdelivery.'];

      // Quit if no search and replace configuration was found
      if (!is_array($configuration['search.']) || !is_array($configuration['replace.'])) {
        return;
      }

      // Replace the content
      $parameters['pObj']->content = preg_replace(
        $configuration['search.'],
        $configuration['replace.'],
        $parameters['pObj']->content
      );
    }
  }
}