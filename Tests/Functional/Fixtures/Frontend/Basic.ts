# === Configuration ===

config {
  # --- Common ---

  xhtml_cleaning = 0

  disableAllHeaderCode = 1

  sendCacheHeaders = 0

  no_cache = 1

  contentObjectExceptionHandler = 0

  # --- URL / Link Handling ---

  # HTTP GET parameters which should be passed on with links in TYPO3.
  linkVars = L(0-1)

  # Prefixes all links with a `/`, resulting in absolute link paths.
  absRefPrefix = /

  # --- L10n ---

  sys_language_uid = 0

  sys_language_isocode = en

  sys_language_isocode_default = en

  language = en

  locale_all = en_US.UTF-8

  sys_language_mode = ignore

  sys_language_overlay = 1

  # --- Misc ---

  # Settings for the spam protection of email addresses:
  spamProtectEmailAddresses              = 1
  spamProtectEmailAddresses_atSubst      = &#64;
  spamProtectEmailAddresses_lastDotSubst = .

  # En-/disables the Admin-Panel.
  admPanel = 0

  # En-/disables the extra debug information as comment in the HTML code.
  debug = 0

  # Disables the prefix comments.
  disablePrefixComment = 1
}

# === Page ===

page = PAGE
page {
  10 = FLUIDTEMPLATE
  10 {
    file = EXT:t3v_delivery/Tests/Functional/Fixtures/Frontend/Template.html
  }
}

# === Locales ===

[globalVar = GP:L = 1]
config {
  sys_language_uid = 1
}
[end]