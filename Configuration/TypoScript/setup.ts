# ------------------------------
# | T3v Delivery Configuration |
# ------------------------------

# === Plugin Configuration ===

plugin {
  tx_t3vdelivery {
    persistence {
      enableAutomaticCacheClearing = {$plugin.tx_t3vdelivery.persistence.enableAutomaticCacheClearing}

      storagePid = {$plugin.tx_t3vdelivery.persistence.storagePid}

      updateReferenceIndex = {$plugin.tx_t3vdelivery.persistence.updateReferenceIndex}
    }

    settings {
      extbase {
        controllerExtensionName = {$plugin.tx_t3vdelivery.settings.extbase.controllerExtensionName}
      }
    }

    view {
      layoutRootPath = {$plugin.tx_t3vdelivery.view.layoutRootPath}

      # Used to define several paths for layouts, which will be tried in reversed order (the paths are searched from
      # bottom to top). The first folder where the desired layout is found, is used. If the array keys are numeric,
      # they are first sorted and then tried in reversed order.
      layoutRootPaths {
        0 = {$plugin.tx_t3vdelivery.view.layoutRootPath}
      }

      templateRootPath = {$plugin.tx_t3vdelivery.view.templateRootPath}

      # Used to define several paths for templates, which will be tried in reversed order (the paths are searched from
      # bottom to top). The first folder where the desired layout is found, is used. If the array keys are numeric,
      # they are first sorted and then tried in reversed order.
      templateRootPaths {
        0 = {$plugin.tx_t3vdelivery.view.templateRootPath}
      }

      partialRootPath = {$plugin.tx_t3vdelivery.view.partialRootPath}

      # Used to define several paths for partials, which will be tried in reversed order. The first folder where the
      # desired partial is found, is used. The keys of the array define the order.
      partialRootPaths {
        0 = {$plugin.tx_t3vdelivery.view.partialRootPath}
      }
    }
  }
}