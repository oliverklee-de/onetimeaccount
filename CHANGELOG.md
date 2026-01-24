# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

- Add a department field (#1111)

### Deprecated

### Changed

- Make the label for "title" less ambiguous (#1110)
- Require feuserextrafields >= 6.6.0 (#1109, #1112)
- Require the latest TYPO3 12LTS security release (#1092, #1170)

### Removed

### Fixed

- Add some top margin to the form (#1114)
- Fix HTML grid for multi-field rows (#1113)

## 7.2.0: Add support for PHP 8.4

### Added

- Add support for PHP 8.4 (#1051)

### Changed

- Allow `typo3fluid/fluid:^4.0` (#1050)
- Require TYPO3 >= 11.5.41 (#1049)

## 7.1.2: Bug fixes and cleanup

### Removed

- Remove unneeded `resname` from the language files (#1031)

## 7.1.1: Bug fixes for TYPO3 12LTS

### Fixed

- Fix the redirect response with TYPO3 12LTS (#1028)

## 7.1.0: Add support for TYPO3 12 LTS

### Added

- Add support for TYPO3 12LTS (#986, #989, #991, #992, #996, #997)

### Changed

- Require oelib >= 6.0 (#985, #1000)

## 7.0.0: Drop support for TYPO3 10LTS and PHP < 7.4

### Added

- Add a field for the VAT IN (#881)
- Record the date of acceptance for the terms and privacy (#880)
- Add a checkbox for the terms & conditions (#877, #879)

### Changed

- Rename some methods: `generate*` -> `generateAndSet` (#976)
- Mark some classes and methods as `@internal` (#974)
- Require Fluid >= 2.7.4 (#962)
- Require TYPO3 >= 11.5.37 (#949)
- Switch to constructor injection (#864, #865)
- Use PHP 7.4 language features and add native type declarations (#817)
- Require `feuserextrafields` >= 6.3.0 (#805, #873)

### Removed

- Drop `AbstractUserController` (#833)
- Drop the plugin with autologin (#827)
- Drop support for TYPO3 10LTS (#797, #958)
- Drop support for PHP 7.2/7.3 (#796)

### Fixed

- Always return a response from the actions (#971)
- Use short class names for Extbase annotations (#978)
- Avoid using the deprecated `$GLOBALS['TSFE']->fe_user` (#968)
- Drop obsolete entries from the `ext_emconf.php` (#860)

## 6.4.0

### Added

- Set the last login date when creating a FE user record (#707)

### Changed

- Allow installations with oelib 6.x (#771)
- Shorten the label for the salutation (#744)
- Unify the spelling of "email" (#761, #762)
- Require higher TYPO3 Core bugfix versions (#713)

### Removed

- Delete localizations that are now on Crowdin (#764)

### Fixed

- Improve accessibility of the form (#791)
- Fix grammar in error message (#784)
- Add `resname` to all language labels (#760)
- Avoid using deprecated oelib functionality (#714)

## 6.3.0

### Added

- Add the Crowdin configuration (#692)
- Add support for PHP 8.3 (#681)

### Changed

- Allow feuserextrafields 6.x (#679)

### Fixed

- Do not skip the Extbase property validations (#704)
- Drop obsolete option from the plugin registration (#703)

## 6.2.1

### Fixed

- Do not inject the `PasswordHashFactory` (#633)
- Mark all injected classes as singletons (#631)

## 6.2.0

### Added

- Add a CAPTCHA (#570)
- Add "diverse" as a gender option (#566)

## 6.1.2

### Deprecated

- Deprecate the plugin with autologin (#559)

### Changed

- Improve the wording in a flexforms label (#562)
- Switch the user validator to recognize `DateTimeInterface` (#560)
- Require feuserextrafields >= 5.3.0 (#556)
- Require oelib >= 5.1.0 (#555)

### Removed

- Make the flexforms more compact by removing the suggest wizards (#561)

### Fixed

- Use proper XML namespaces for the custom view helper in the Fluid templates
  (#564)

## 6.1.1

### Changed

- Bump the dependencies (#551)
- Make the abstract classes `@internal` (#538)
- Switch the coverage on CI from Xdebug to PCOV (#501)

### Removed

- Stop using Prophecy (#519, #520, #521, #522)

### Fixed

- Drop the 9TLS-only plugin element registration (#550)
- Enable caching for PHP-CS-Fixer (#536)
- Improve type safety (#508)

## 6.1.0

### Added

- Add support for PHP 8.2 (#489)
- Add support for TYPO3 11LTS (#481, #485, #486, #490)

### Changed

- Require oelib >= 5.0 (#477)

### Removed

- Make displaying the group selector non-configurable (#479)
- Drop the elaborate validation for the user group (#478)

### Fixed

- Add more native types to a testing support class (#482)

## 6.0.0

### Added

- Add a selector for the user group (#460, #467, #468, #471)
- Add a Rector configuration file (#454)

### Changed

- Require feuserextrafields >= 5.2.1 (#435, #446, #453, #456)

### Removed

- Drop support for TYPO3 9LTS (#438)

### Fixed

- Stop using the deprecated `ObjectManager` in tests (#444)
- Stop using the deprecated `TYPO3_MODE` constant (#443)
- Fix the plugin registration for TYPO3 10LTS/11LTS (#440)

## 5.1.1

### Changed

- Allow oelib 5.x (#430)
- Require oelib >= 4.3.0 (#429)

## 5.1.0

### Added

- Add an automatic configuration check (#408)
- Add more localized labels (#396)
- Add maxlength attributes to the HTML to match the fields in the DB (#395)
- Add a plugin that writes the user ID into the session (without autologin)
  (#390)

### Changed

- Add a dependency on oelib (#405, #406, #407)
- !!! Move the localized validation error messages from the partial to the
  validator (#404)
- Allow installations with feuserextrafields 5 (#371)
- Require feuserextrafields >= 3.2.0 (#345)

### Fixed

- Allow XCLASSing the FE user class (#413)
- Bump the minimal 10.4 Extbase requirement (#366)

## 5.0.0

### Added

- Complete rewrite with added support for TYPO3 9LTS and 10LTS

### Removed

- Drop support for TYPO3 8TLS

## 4.2.2

### Added

- Add an `.editorconfig` to match the Core (#183)

### Changed

- Update the dependencies (#193)
- Require oelib >= 3.6.3 (#182, #189)

### Fixed

- Allow the development Composer plugins (#192)

## 4.2.1

### Changed

- Require oelib >= 3.2.0 (#180)

### Fixed

- Relax the dependencies to allow non-Composer installations again (#181)

## 4.2.0

### Added

- Add support for PHP 7.3 and 7.4 (#177)

### Changed

- Update the dependencies (#175, #176)
- Switch from Travis CI to GitHub Actions (#173, #174)
- Switch the default branch from master to main (#172)

## 4.1.1

### Fixed

- Add `.0` version suffixes to PHP version requirements (#161)
- Update the "for" attribute of labels (#156, #160)
- Load the flexforms language labels in the default language as well
  (#158, #159)
- Always use Composer-installed versions of the dev tools (#157)

## 4.1.0

### Added

- Add a privacy policy checkbox (#152)

### Changed

- Change the HTML template from XHTML to HTML5 (#151)
- Stop using the TYPO3 console for the CI build (#146)
- Upgrade PHPUnit and nimut/testing-framework (#143)
- Improve the code autoformatting (#139)

### Fixed

- Fix the layout of checkboxes in the FE (#150)
- Fix a typo in the documentation (#149)
- Keep the order of the FE user groups (#147)
- Fix warnings in the `.travis.yml` (#145)
- Do not cache `vendor/` on Travis CI (#142)
- Do not crash if no user groups are selected in the flexforms (#137, #138)

## 4.0.1

### Added

- Add php-cs-fixer to the CI (#131, #132)

### Changed

- Sort the entries in the `.gitignore` and `.gitattributes` (#134)
- Require oelib >= 3.0.3 (#133)
- Use PHP 7.2 for the TER release script (#130)

### Fixed

- Update the locations of the mkforms JavaScript includes (#135)

## 4.0.0

### Added

- Add support for TYPO3 9LTS (#123)
- Add code sniffing and fixing (#120)
- Build with PHP 7.2 on Travis CI (#115)
- Display the name of the current functional test (#97, #98)

### Changed

- Use the new configuration check (#122, #79)
- Bump the `static_info_tables` dependency (#121)
- Require oelib 3.0 (#119)
- Use the EXT: notation for language files (#76, #117)
- Sort the Composer dependencies (#111)
- Allow 9.5-compatible versions of mkforms and rn_base (#108)
- Update the testing libraries (#93, #96)

### Removed

- Skip the logging in TYPO3 >= 9 (#127)
- Drop unneeded Travis CI configuration settings (#99, #100, #101)
- Drop support for PHP 5 (#92, #114)
- Drop support for TYPO3 7.6 (#91, #104, #113)

### Fixed

- Avoid using deprecated Core functionality in TYPO3 9LTS (#128)
- Use the new mock class name (#126)
- Move the plugin registration to `Configuration/TCA/Overrides/` (#125)
- Fix the path to the content element icon (#124)
- git-ignore the tests-generated `var/log/` folder (#118)
- Allow an empty name if the field is hidden or non-required (#105, #106)
- Add a dependency on typo3/cms-lang (#86, #94)

## 3.0.2

### Changed

- Simplify the dependency versions in composer.json (#87)
- Use the nimut TF for creating test records (#83)
- Update the oelib dependency (#80)
- Convert the tests to nimut/testing-framework (#74)
- Use CamelCase in the PHP namespace (#73)
- Upgrade to PHPUnit 5.7 (#71)
- Update the mkforms dependency (#70)
- Change from GPL V3+ to GPL V2+ (#67)
- Require oelib >= 2.3.0 (#63)
- Use spaces for indenting HTML and .htaccess files (#60)
- Streamline ext_emconf.php (#58)

### Removed

- Drop the TYPO3 package repository from composer.json (#72)

### Fixed

- Show the name label if first and last name are visible (#64, #89)
- Drop surplus trailing slashes from Composer scripts (#77, #88)
- Add the transitive dependencies to composer.json (#85)
- Use the ConnectionPool for database queries in TYPO3 8LTS (#84)
- Use the correct name for the extension icon file (#82)
- Keep development files out of the packages (#81)
- Clean up and lint the TypoScript (#75)
- Pin the dev dependency versions (#88)
- Enable MySQL on Travis CI (#66)
- Explicitly provide the extension name in the composer.json (#85)
- Also provide the extension icon in `Resources/` (#62)
- Add a dependency to felogin (#59)

## 3.0.1

### Added

- Auto-release to the TER (#48)

### Changed

- Move the old tests to Tests/LegacyUnit/ (#55)

### Fixed

- Drop an obsolete "replace" entry from composer.json (#68)
- Don't HTML-encode the data from the FE editor on saving (#56)
- Use the current composer names of static_info_tables (#54)
- Add a conflict with a PHP-7.0-incompatible static_info_tables version (#51)
- Update the composer package name of static-info-tables (#47)
- Stop PHP-linting the removed Migrations/ folder (#46)
- Use the public mkforms render method (#45)

## 3.0.0

### Added

- Add support for TYPO3 8.7 (#41)
- Add support for PHP 7.1 and 7.2 (#36)

### Changed

- Also allow oelib 3.x (#43)
- Switch to the SVG icon in the BE for the content element (#38)
- Require oelib >= 2.0.0 (#35)
- Require static_info_tables >= 6.4.0 (#33)
- Update to PHPUnit 5.3 (#31)

### Removed

- Remove the class alias map (#38)
- Drop ext_autoload.php (#34)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#32)
- Drop support for PHP 5.5 (#29)

### Fixed

- Update the content element wizard for TYPO3 8.7 (#42)
- Fix the redirect_url on TravisCI on TYPO3 8.7 (#40)
- Make the PHPUnit test runner configurable (#37)

## 2.1.0

### Added

- Run the unit tests on TravisCI (#12)
- Add an SVG extension icon (#15)
- Composer script for PHP linting (#5)
- Add TravisCI builds

### Changed

- Require oelib ^1.4.0 (#21)
- Require mkforms >= 3.0.0 (#4)
- Require static_info_tables >= 6.3.7 (#2)
- Move the extension to GitHub

### Removed

- Drop the incorrect TYPO3 Core license headers (#20)
- Remove the example extension (#6)

### Fixed

- Fix PhpStorm code inspection warnings (#27)
- Use a public method instead of the internal _getThisFormData (#25)
- Update use of deprecated rn_base configuration class (#24)
- Fix the unit tests concerning the redirect URL on CLI (#22)
- Provide cli_dispatch.phpsh for 8.7 on Travis (#18)
- Require typo3/minimal for installing TYPO3 (#17)
- Require mkforms >= 3.0.14 (#14)
- Fix autoloading when running the tests in the BE module in non-composer mode
  (#11)
- Use the correct package name for mkforms in composer.json
- Allow mkforms 3.x

## 2.0.0

The [change log up to version 2.0.0](Documentation/changelog-archive.txt)
has been archived.
