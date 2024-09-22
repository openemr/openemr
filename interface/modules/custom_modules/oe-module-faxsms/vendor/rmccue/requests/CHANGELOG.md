Changelog
=========

2.0.12
------

### Overview of changes
- Update bundled certificates as of 2024-07-02. [#877]

[#877]: https://github.com/WordPress/Requests/pull/877

2.0.11
------

### Overview of changes
- Update bundled certificates as of 2024-03-11. [#864]
- Fixed: PHP 8.4 deprecation of the two parameter signature of `stream_context_set_option()`. [#822] Props [@jrfnl][gh-jrfnl]
- Fixed: PHP 8.4 deprecation of implicitly nullable parameter. [#865] Props [@Ayesh][gh-ayesh], [@jrfnl][gh-jrfnl]
    Note: this fix constitutes an, albeit small, breaking change to the signature of the `Cookie::parse_from_headers()` method.
    Classes which extend the `Cookie` class and overload the `parse_from_headers()` method should be updated for the new method signature.
    Additionally, if code calling the `Cookie::parse_from_headers()` method would be wrapped in a `try - catch` to catch a potential PHP `TypeError` (PHP 7.0+) or `Exception` (PHP < 7.0) for when invalid data was passed as the `$origin` parameter, this code will need to be updated to now also catch a potential `WpOrg\Requests\Exception\InvalidArgumentException`.
    As due diligence could not find any classes which would be affected by this BC-break, we have deemed it acceptable to include this fix in the 2.0.11 release.

[#822]: https://github.com/WordPress/Requests/pull/822
[#864]: https://github.com/WordPress/Requests/pull/864
[#865]: https://github.com/WordPress/Requests/pull/865

2.0.10
------

### Overview of changes
- Update bundled certificates as of 2023-12-04. [#850]

[#850]: https://github.com/WordPress/Requests/pull/850

2.0.9
-----

### Overview of changes
- Hotfix: Rollback changes from PR [#657]. [#839] Props [@tomsommer][gh-tomsommer] & [@laszlof][gh-laszlof]

[#839]: https://github.com/WordPress/Requests/pull/839

2.0.8
-----

### Overview of changes
- Update bundled certificates as of 2023-08-22. [#823]
- Fixed: only force close cURL connection when needed (cURL < 7.22). [#656], [#657] Props [@mircobabini][gh-mircobabini]
- Composer: updated list of suggested PHP extensions to enable. [#821]
- README: add information about the PSR-7/PSR-18 wrapper for Requests. [#827]

[#656]: https://github.com/WordPress/Requests/pull/656
[#657]: https://github.com/WordPress/Requests/pull/657
[#821]: https://github.com/WordPress/Requests/pull/821
[#823]: https://github.com/WordPress/Requests/pull/823
[#827]: https://github.com/WordPress/Requests/pull/827

2.0.7
-----

### Overview of changes
- Update bundled certificates as of 2023-05-30. [#809]

[#809]: https://github.com/WordPress/Requests/pull/809

2.0.6
-----

### Overview of changes
- Update bundled certificates as of 2023-01-10. [#791]
- Fix typo in deprecation notice. [#785] Props [@costdev][gh-costdev]
- Minor internal improvements for passing the correct type to function calls. [#779]
- Confirmed compatibility with PHP 8.2.
    No changes were needed, so Request 2.0.1 and higher can be considered compatible with PHP 8.2.
- Various documentation improvements and other general housekeeping.

[#779]: https://github.com/WordPress/Requests/pull/779
[#785]: https://github.com/WordPress/Requests/pull/785
[#791]: https://github.com/WordPress/Requests/pull/791

2.0.5
-----

### Overview of changes
- Update bundled certificates as of 2022-10-11. [#769]

[#769]: https://github.com/WordPress/Requests/pull/769

2.0.4
-----

### Overview of changes
- Update bundled certificates as of 2022-07-19. [#763]

[#763]: https://github.com/WordPress/Requests/pull/763

2.0.3
-----

### Overview of changes
- Update bundled certificates as of 2022-04-26. [#731]

[#731]: https://github.com/WordPress/Requests/pull/731

2.0.2
-----

### Overview of changes
- Update bundled certificates as of 2022-03-18. [#697]

[#697]: https://github.com/WordPress/Requests/pull/697

2.0.1
-----

### Overview of changes
- Update bundled certificates as of 2022-02-01. [#670]
- Bug fix: Hook priority should be respected. [#452], [#647]
- Docs: the Hook documentation has been updated to reflect the current available hooks. [#646]
- General housekeeping. [#635], [#649], [#650], [#653], [#655], [#658], [#660], [#661], [#662], [#669], [#671], [#672], [#674]

Props [@alpipego][gh-alpipego], [@costdev][gh-costdev], [@jegrandet][gh-jegrandet], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera]

[#674]: https://github.com/WordPress/Requests/pull/674
[#672]: https://github.com/WordPress/Requests/pull/672
[#671]: https://github.com/WordPress/Requests/pull/671
[#670]: https://github.com/WordPress/Requests/pull/670
[#669]: https://github.com/WordPress/Requests/pull/669
[#662]: https://github.com/WordPress/Requests/pull/662
[#661]: https://github.com/WordPress/Requests/pull/661
[#660]: https://github.com/WordPress/Requests/pull/660
[#658]: https://github.com/WordPress/Requests/pull/658
[#655]: https://github.com/WordPress/Requests/pull/655
[#653]: https://github.com/WordPress/Requests/pull/653
[#650]: https://github.com/WordPress/Requests/pull/650
[#649]: https://github.com/WordPress/Requests/pull/649
[#647]: https://github.com/WordPress/Requests/pull/647
[#646]: https://github.com/WordPress/Requests/pull/646
[#635]: https://github.com/WordPress/Requests/issues/635
[#452]: https://github.com/WordPress/Requests/issues/452


2.0.0
-----

### BREAKING CHANGES

As Requests 2.0.0 is a major release, this version contains breaking changes. There is an [upgrade guide](https://requests.ryanmccue.info/docs/upgrading.html) available to guide you through making the necessary changes in your own code.

### Overview of changes

- **New minimum PHP version**

  Support for PHP 5.2 - 5.5 has been dropped. The new minimum supported PHP version is now 5.6.

  Support for HHVM has also been dropped formally now.

  (props [@datagutten][gh-datagutten], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#378][gh-378], [#470][gh-470], [#509][gh-509])

- **New release branch name**

  The stable version of Requests can be found in the `stable` branch (was `master`).
  Development of Requests happens in the `develop` branch.

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#463][gh-463], [#490][gh-490])

- **All code is now namespaced (PSR-4)**

  The code within the Requests library has all been namespaced and now lives in the `WpOrg\Requests` namespace.

  The namespaced classes can be found in the `src` directory. The old `library` directory and the files within are deprecated.

  For a number of classes, some subtle changes have also been made to their base class name, like renaming the `Hooker` interface to `HookManager`.

  A full backward-compatibility layer is available and using the non-namespaced class names will still work during the 2.x and 3.x release cycles, though a deprecation notice will be thrown the first time a class using one of the old PSR-0 based class names is requested.
  For the lifetime of Requests 2.x, the deprecation notices can be disabled by defining a global `REQUESTS_SILENCE_PSR0_DEPRECATIONS` constant and
setting the value of this constant to `true`.

  A complete "translation table" between the Requests 1.x and 2.x class names is available in the [upgrade guide](https://requests.ryanmccue.info/docs/upgrading.html).

  Users of the Requests native custom autoloader will need to adjust their code to initialize the autoloader:
  ```php
  // OLD: Using the custom autoloader in Requests 1.x.
  require_once 'path/to/Requests/library/Requests.php';
  Requests::register_autoloader();

  // NEW: Using the custom autoloader in Requests 2.x.
  require_once 'path/to/Requests/src/Autoload.php';
  WpOrg\Requests\Autoload::register();
  ```

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#503][gh-503], [#519][gh-519], [#586][gh-586], [#587][gh-587], [#594][gh-594])

- **A large number of classes have been marked as `final`**

  Marking a class as `final` prohibits extending it.

  These changes were made after researching which classes were being extended in userland code and due diligence has been applied before making these changes. If this change is causing a problem we didn't anticipate, please [open an issue to report it](https://github.com/WordPress/Requests/issues/new/choose).

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#514][gh-514], [#534][gh-534])

- **Input validation**

  All typical entry point methods in Requests will now, directly or indirectly, validate the received input parameters for being of the correct type.
  When an incorrect parameter type is received, a catchable `WpOrg\Requests\Exception\InvalidArgument` exception will be thrown.

  The input validation has been set up to be reasonably liberal, so if Requests was being used as per the documentation, this change should not affect you.
  If you still find the input validation to be too strict and you have a good use-case of why it should be loosened for a particular entry point, please [open an issue to discuss this](https://github.com/WordPress/Requests/issues/new/choose).

  The code within Requests itself has also received various improvements to be more type safe.

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#499][gh-499], [#542][gh-542], [#547][gh-547], [#558][gh-558], [#572][gh-572], [#573][gh-573], [#574][gh-574], [#591][gh-591], [#592][gh-592], [#593][gh-593], [#601][gh-601], [#602][gh-602], [#603][gh-603], [#604][gh-604], [#605][gh-605], [#609][gh-609], [#610][gh-610], [#611][gh-611], [#613][gh-613], [#614][gh-614], [#615][gh-615], [#620][gh-620], [#621][gh-621], [#629][gh-629])

- **Update bundled certificates**

  The bundled certificates were updated with the latest version available (published 2021-10-26).

  Previously the bundled certificates in Requests would include a small subset of expired certificates for legacy reasons.
  This is no longer the case as of Requests 2.0.0.

  > :warning: **Note**: the included certificates bundle is only intended as a fallback.
  >
  > This fallback should only be used for servers that are not properly configured for SSL verification. A continuously managed server should provide a more up-to-date certificate authority list than a software library which only gets updates once in a while.
  >
  > Setting the `$options['verify']` key to `true` when initiating a request enables certificate verification using the certificate authority list provided by the server environment, which is recommended.

  The [documentation regarding Secure Requests with SSL](https://requests.ryanmccue.info/docs/usage-advanced.html#secure-requests-with-ssl) has also been updated to reflect this and it is recommended to have a read through.

  The included certificates _file_ has now also been moved to a dedicated `/certificates` directory off the project root.

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [@wojsmol][gh-wojsmol], [@ZsgsDesign][gh-ZsgsDesign], [#535][gh-535], [#571][gh-571], [#577][gh-577], [#622][gh-622], [#632][gh-632])

- **New functionality**

  The following new functionality has been added:
  - A `public static` `WpOrg\Requests\Requests::has_capabilities($capabilities = array())` method is now available to check whether there is a transport available which supports the requested capabilities.
  - A `public` `WpOrg\Requests\Response::decode_body($associative = true, $depth = 512, $options = 0)` method is now available to handle JSON-decoding a response body.
    The method parameters correspond to the parameters of the PHP native [`json_decode()`](https://php.net/json-decode) function.
    The method will throw an `WpOrg\Requests\Exception` when the response body is not valid JSON.
  - A `WpOrg\Requests\Capability` interface. This interface provides constants for the known capabilities. Transports can be tested whether or not they support these capabilities.
    Currently, the only capability supported is `Capability::SSL`.
  - A `WpOrg\Requests\Port` class. This class encapsulates typical port numbers as constants and offers a `static` `Port::get($type)` method to retrieve a port number based on a request type.
    Using this class when referring to port numbers is recommended.
  - An `WpOrg\Requests\Exceptions\InvalidArgument` class. This class is intended for internal use only.
  - An `WpOrg\Requests\Utility\InputValidator` class with helper methods for input validation. This class is intended for internal use only.

  (props [@ccrims0n][gh-ccrims0n], [@dd32][gh-dd32], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#167][gh-167], [#214][gh-214], [#250][gh-250], [#251][gh-251], [#492][gh-492], [#499][gh-499], [#538][gh-538], [#542][gh-542], [#547][gh-547], [#559][gh-559])

- **Changed functionality**

  - The `WpOrg\Requests\Requests::decompress()` method has been fixed to recognize more compression levels and handle these correctly.
  - The method signature of the `WpOrg\Requests\Transport::test()` interface method has been adjusted to enforce support for an optional `$capabilities` parameter.
    The Request native `WpOrg\Requests\Transport\Curl::test()` and `WpOrg\Requests\Transport\Fsockopen::test()` methods both already supported this parameter.
  - The `WpOrg\Requests\Transport\Curl::request()` and the `WpOrg\Requests\Transport\Fsockopen::request()` methods will now throw an `WpOrg\Requests\Exception` when the `$options['filename']` contains an invalid path.
  - The `WpOrg\Requests\Transport\Curl::request()` method will no longer set the `CURLOPT_REFERER` option.
  - The default value of the `$key` parameter in the `WpOrg\Requests\Cookie\Jar::normalize_cookie()` method has been changed from `null` to an empty string.

  (props [@datagutten][gh-datagutten], [@dustinrue][gh-dustinrue], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [@soulseekah][gh-soulseekah], [@twdnhfr][gh-twdnhfr], [#301][gh-301], [#309][gh-309], [#379][gh-379], [#444][gh-444], [#492][gh-492], [#610][gh-610])

- **Removed functionality**

  The following methods, which were deprecated during the 1.x cycle, have now been removed:
  - `Requests::flattern()`, use `WpOrg\Requests\Requests::flatten()` instead.
  - `Requests_Cookie::formatForHeader()`, use `WpOrg\Requests\Cookie::format_for_header()` instead.
  - `Requests_Cookie::formatForSetCookie()`, use `WpOrg\Requests\Cookie::format_for_set_cookie()` instead.
  - `Requests_Cookie::parseFromHeaders()`, use `WpOrg\Requests\Cookie::parse_from_headers()` instead.
  - `Requests_Cookie_Jar::normalizeCookie()`, use `WpOrg\Requests\Cookie\Jar::normalize_cookie()` instead

  A duplicate method has been removed:
  - `Requests::match_domain()`, use `WpOrg\Requests\Ssl::match_domain()` instead.

  A redundant method has been removed:
  - `Hooks::__construct()`.

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#510][gh-510], [#525][gh-525], [#617][gh-617])

- **Compatibility with PHP 8.0 named parameters**

  All parameter names have been reviewed to prevent issues for users using PHP 8.0 named parameters and where relevant, a number of parameter names have been changed.

  After this release, a parameter name rename will be treated as a breaking change (reserved for major releases) and will be marked as such in the changelog.

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#533][gh-533], [#560][gh-560], [#561][gh-561], [#599][gh-599], [#612][gh-612])

- **PHP 8.1 compatibility**

  All known PHP 8.1 compatibility issues have been fixed and tests are now running (and passing) against PHP 8.1.

  In case you still run into a PHP 8.1 deprecation notice or other PHP 8.1 related issue, please [open an issue to report it](https://github.com/WordPress/Requests/issues/new/choose).

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#498][gh-498], [#499][gh-499], [#500][gh-500], [#501][gh-501], [#505][gh-505], [#634][gh-634])

- **Updated documentation**

  The [documentation website](https://requests.ryanmccue.info/) has been updated to reflect all the changes in Requests 2.0.0.

  The [API documentation for Requests 2.x](https://requests.ryanmccue.info/api-2.x/) is now generated using [phpDocumentor](https://www.phpdoc.org/) :heart: and available on the website.
  For the time being, the [Requests 1.x API documentation](https://requests.ryanmccue.info/api/) will still be available on the website as well.

  (props [@costdev][gh-costdev], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [@szepeviktor][gh-szepeviktor], [#476][gh-476], [#480][gh-480], [#489][gh-489], [#495][gh-495], [#526][gh-526], [#528][gh-528], [#532][gh-532], [#543][gh-543], [#562][gh-562], [#578][gh-578], [#590][gh-590], [#606][gh-606], [#607][gh-607], [#608][gh-608], [#618][gh-618], [#622][gh-622], [#625][gh-625], [#626][gh-626], [#630][gh-630], [#642][gh-642])

- **General housekeeping**

  - In a number of places, code modernizations, possible now the minimum PHP version has gone up to PHP 5.6, have been applied.
    ([#504][gh-504], [#506][gh-506], [#512][gh-512], [#539][gh-539], [#541][gh-541], [#599][gh-599], [#623][gh-623])

  - Lots of improvements were made to render the tests more reliable and increase the coverage.
    ([#446][gh-446], [#459][gh-459], [#472][gh-472], [#503][gh-503], [#508][gh-508], [#511][gh-511], [#520][gh-520], [#521][gh-521], [#548][gh-548], [#549][gh-549], [#550][gh-550], [#551][gh-551], [#552][gh-552], [#553][gh-553], [#554][gh-554], [#555][gh-555], [#556][gh-556], [#557][gh-557], [#558][gh-558], [#566][gh-566], [#581][gh-581], [#591][gh-591], [#595][gh-595], [#640][gh-640])

  - The move for all CI to GitHub Actions has been finalized. Travis is dead, long live Travis and thanks for all the fish.
    ([#447][gh-447], [#575][gh-575], [#579][gh-579])

  - A GitHub Actions workflow has been put in place to allow for automatically updating the website on releases.
    This should allow for more rapid releases from now on.
    ([#466][gh-466], [#544][gh-544], [#545][gh-545], [#563][gh-563], [#569][gh-569], [#583][gh-583], [#626][gh-626])

  - Development-only dependencies have been updated.
    ([#516][gh-516], [#517][gh-517])

  - Various other general housekeeping and improvements for contributors.
    ([#488][gh-488], [#491][gh-491], [#523][gh-523], [#513][gh-513], [#515][gh-515], [#522][gh-522], [#524][gh-524], [#531][gh-531], [#535][gh-535], [#536][gh-536], [#537][gh-537], [#540][gh-540], [#588][gh-588], [#616][gh-616])

  (props [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera])

[gh-642]: https://github.com/WordPress/Requests/pull/642
[gh-640]: https://github.com/WordPress/Requests/pull/640
[gh-634]: https://github.com/WordPress/Requests/pull/634
[gh-632]: https://github.com/WordPress/Requests/pull/632
[gh-630]: https://github.com/WordPress/Requests/pull/630
[gh-629]: https://github.com/WordPress/Requests/pull/629
[gh-626]: https://github.com/WordPress/Requests/pull/626
[gh-625]: https://github.com/WordPress/Requests/pull/625
[gh-623]: https://github.com/WordPress/Requests/pull/623
[gh-622]: https://github.com/WordPress/Requests/pull/622
[gh-621]: https://github.com/WordPress/Requests/pull/621
[gh-620]: https://github.com/WordPress/Requests/pull/620
[gh-618]: https://github.com/WordPress/Requests/pull/618
[gh-617]: https://github.com/WordPress/Requests/pull/617
[gh-616]: https://github.com/WordPress/Requests/pull/616
[gh-615]: https://github.com/WordPress/Requests/pull/615
[gh-614]: https://github.com/WordPress/Requests/pull/614
[gh-613]: https://github.com/WordPress/Requests/pull/613
[gh-612]: https://github.com/WordPress/Requests/pull/612
[gh-611]: https://github.com/WordPress/Requests/pull/611
[gh-610]: https://github.com/WordPress/Requests/pull/610
[gh-609]: https://github.com/WordPress/Requests/pull/609
[gh-608]: https://github.com/WordPress/Requests/pull/608
[gh-607]: https://github.com/WordPress/Requests/pull/607
[gh-606]: https://github.com/WordPress/Requests/pull/606
[gh-605]: https://github.com/WordPress/Requests/pull/605
[gh-604]: https://github.com/WordPress/Requests/pull/604
[gh-603]: https://github.com/WordPress/Requests/pull/603
[gh-602]: https://github.com/WordPress/Requests/pull/602
[gh-601]: https://github.com/WordPress/Requests/pull/601
[gh-599]: https://github.com/WordPress/Requests/pull/599
[gh-595]: https://github.com/WordPress/Requests/pull/595
[gh-594]: https://github.com/WordPress/Requests/pull/594
[gh-593]: https://github.com/WordPress/Requests/issues/593
[gh-592]: https://github.com/WordPress/Requests/pull/592
[gh-591]: https://github.com/WordPress/Requests/pull/591
[gh-590]: https://github.com/WordPress/Requests/issues/590
[gh-588]: https://github.com/WordPress/Requests/pull/588
[gh-587]: https://github.com/WordPress/Requests/pull/587
[gh-586]: https://github.com/WordPress/Requests/pull/586
[gh-583]: https://github.com/WordPress/Requests/pull/583
[gh-581]: https://github.com/WordPress/Requests/pull/581
[gh-579]: https://github.com/WordPress/Requests/pull/579
[gh-578]: https://github.com/WordPress/Requests/pull/578
[gh-577]: https://github.com/WordPress/Requests/pull/577
[gh-575]: https://github.com/WordPress/Requests/pull/575
[gh-574]: https://github.com/WordPress/Requests/pull/574
[gh-573]: https://github.com/WordPress/Requests/pull/573
[gh-572]: https://github.com/WordPress/Requests/pull/572
[gh-571]: https://github.com/WordPress/Requests/pull/571
[gh-569]: https://github.com/WordPress/Requests/pull/569
[gh-566]: https://github.com/WordPress/Requests/pull/566
[gh-563]: https://github.com/WordPress/Requests/pull/563
[gh-562]: https://github.com/WordPress/Requests/pull/562
[gh-561]: https://github.com/WordPress/Requests/pull/561
[gh-560]: https://github.com/WordPress/Requests/pull/560
[gh-559]: https://github.com/WordPress/Requests/pull/559
[gh-558]: https://github.com/WordPress/Requests/pull/558
[gh-557]: https://github.com/WordPress/Requests/pull/557
[gh-556]: https://github.com/WordPress/Requests/pull/556
[gh-555]: https://github.com/WordPress/Requests/pull/555
[gh-554]: https://github.com/WordPress/Requests/pull/554
[gh-553]: https://github.com/WordPress/Requests/pull/553
[gh-552]: https://github.com/WordPress/Requests/pull/552
[gh-551]: https://github.com/WordPress/Requests/pull/551
[gh-550]: https://github.com/WordPress/Requests/pull/550
[gh-549]: https://github.com/WordPress/Requests/pull/549
[gh-548]: https://github.com/WordPress/Requests/pull/548
[gh-547]: https://github.com/WordPress/Requests/pull/547
[gh-545]: https://github.com/WordPress/Requests/pull/545
[gh-544]: https://github.com/WordPress/Requests/pull/544
[gh-543]: https://github.com/WordPress/Requests/pull/543
[gh-542]: https://github.com/WordPress/Requests/pull/542
[gh-541]: https://github.com/WordPress/Requests/pull/541
[gh-540]: https://github.com/WordPress/Requests/pull/540
[gh-539]: https://github.com/WordPress/Requests/pull/539
[gh-538]: https://github.com/WordPress/Requests/pull/538
[gh-537]: https://github.com/WordPress/Requests/pull/537
[gh-536]: https://github.com/WordPress/Requests/pull/536
[gh-535]: https://github.com/WordPress/Requests/pull/535
[gh-534]: https://github.com/WordPress/Requests/pull/534
[gh-533]: https://github.com/WordPress/Requests/issues/533
[gh-532]: https://github.com/WordPress/Requests/pull/532
[gh-531]: https://github.com/WordPress/Requests/pull/531
[gh-528]: https://github.com/WordPress/Requests/pull/528
[gh-526]: https://github.com/WordPress/Requests/pull/526
[gh-525]: https://github.com/WordPress/Requests/pull/525
[gh-524]: https://github.com/WordPress/Requests/pull/524
[gh-523]: https://github.com/WordPress/Requests/pull/523
[gh-522]: https://github.com/WordPress/Requests/pull/522
[gh-521]: https://github.com/WordPress/Requests/pull/521
[gh-520]: https://github.com/WordPress/Requests/pull/520
[gh-519]: https://github.com/WordPress/Requests/pull/519
[gh-517]: https://github.com/WordPress/Requests/pull/517
[gh-516]: https://github.com/WordPress/Requests/pull/516
[gh-515]: https://github.com/WordPress/Requests/issues/515
[gh-514]: https://github.com/WordPress/Requests/issues/514
[gh-513]: https://github.com/WordPress/Requests/issues/513
[gh-512]: https://github.com/WordPress/Requests/issues/512
[gh-511]: https://github.com/WordPress/Requests/pull/511
[gh-510]: https://github.com/WordPress/Requests/pull/510
[gh-509]: https://github.com/WordPress/Requests/pull/509
[gh-508]: https://github.com/WordPress/Requests/pull/508
[gh-506]: https://github.com/WordPress/Requests/pull/506
[gh-505]: https://github.com/WordPress/Requests/pull/505
[gh-504]: https://github.com/WordPress/Requests/pull/504
[gh-503]: https://github.com/WordPress/Requests/pull/503
[gh-501]: https://github.com/WordPress/Requests/pull/501
[gh-500]: https://github.com/WordPress/Requests/pull/500
[gh-499]: https://github.com/WordPress/Requests/pull/499
[gh-498]: https://github.com/WordPress/Requests/issues/498
[gh-498]: https://github.com/WordPress/Requests/issues/495
[gh-492]: https://github.com/WordPress/Requests/pull/492
[gh-491]: https://github.com/WordPress/Requests/pull/491
[gh-490]: https://github.com/WordPress/Requests/pull/490
[gh-489]: https://github.com/WordPress/Requests/pull/489
[gh-488]: https://github.com/WordPress/Requests/pull/488
[gh-480]: https://github.com/WordPress/Requests/issues/480
[gh-476]: https://github.com/WordPress/Requests/issues/476
[gh-472]: https://github.com/WordPress/Requests/issues/472
[gh-470]: https://github.com/WordPress/Requests/pull/470
[gh-466]: https://github.com/WordPress/Requests/issues/466
[gh-463]: https://github.com/WordPress/Requests/issues/463
[gh-460]: https://github.com/WordPress/Requests/issues/460
[gh-459]: https://github.com/WordPress/Requests/issues/459
[gh-447]: https://github.com/WordPress/Requests/pull/447
[gh-446]: https://github.com/WordPress/Requests/pull/446
[gh-444]: https://github.com/WordPress/Requests/pull/444
[gh-379]: https://github.com/WordPress/Requests/pull/379
[gh-378]: https://github.com/WordPress/Requests/issues/378
[gh-309]: https://github.com/WordPress/Requests/pull/309
[gh-301]: https://github.com/WordPress/Requests/issues/301
[gh-251]: https://github.com/WordPress/Requests/pull/251
[gh-250]: https://github.com/WordPress/Requests/issues/250
[gh-214]: https://github.com/WordPress/Requests/pull/214
[gh-167]: https://github.com/WordPress/Requests/issues/167

1.8.1
-----

### Overview of changes
- The `Requests::VERSION` constant has been updated to reflect the actual version for the release. [@jrfnl][gh-jrfnl], [#485][gh-485]
- Update the `.gitattributes` file to include fewer files in the distribution. [@mbabker][gh-mbabker], [#484][gh-484]
- Added a release checklist. [@jrfnl][gh-jrfnl], [#483][gh-483]
- Various minor updates to the documentation and the website. [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#477][gh-477], [#478][gh-478], [#479][gh-479], [#481][gh-481], [#482][gh-482]

[gh-477]: https://github.com/WordPress/Requests/issues/477
[gh-478]: https://github.com/WordPress/Requests/issues/478
[gh-479]: https://github.com/WordPress/Requests/issues/479
[gh-481]: https://github.com/WordPress/Requests/issues/481
[gh-482]: https://github.com/WordPress/Requests/issues/482
[gh-483]: https://github.com/WordPress/Requests/issues/483
[gh-484]: https://github.com/WordPress/Requests/issues/484
[gh-485]: https://github.com/WordPress/Requests/issues/485


1.8.0
-----

### IMPORTANT NOTES

#### Last release supporting PHP 5.2 - 5.5

  Release 1.8.0 will be the last release with compatibility for PHP 5.2 - 5.5. With the next release (v2.0.0), the minimum PHP version will be bumped to 5.6.

#### Last release supporting PEAR distribution

  Release 1.8.0 will be the last release to be distributed via PEAR. From release 2.0.0 onwards, consumers of this library will have to switch to Composer to receive updates.

### Overview of changes

- **[SECURITY FIX] Disable deserialization in `FilteredIterator`**

  A `Deserialization of Untrusted Data` weakness was found in the `FilteredIterator` class.

  This security vulnerability was first reported to the WordPress project. The security fix applied to WordPress has been ported back into the library.

  GitHub security advisory: [Insecure Deserialization of untrusted data](https://github.com/WordPress/Requests/security/advisories/GHSA-52qp-jpq7-6c54)

  CVE: [CVE-2021-29476 - Deserialization of Untrusted Data](https://cve.mitre.org/cgi-bin/cvename.cgi?name=2021-29476)

  Related WordPress CVE: [https://cve.mitre.org/cgi-bin/cvename.cgi?name=2020-28032](https://cve.mitre.org/cgi-bin/cvename.cgi?name=2020-28032)

  (props [@dd32][gh-dd32], [@desrosj][gh-desrosj], [@jrfnl][gh-jrfnl], [@peterwilsoncc][gh-peterwilsoncc], [@SergeyBiryukov][gh-SergeyBiryukov], [@whyisjake][gh-whyisjake], [@xknown][gh-xknown], [#421][gh-421], [#422][gh-422])


- **Repository moved to `WordPress\Requests`**

  The `Requests` library has been moved to the WordPress GitHub organization and can now be found under `https://github.com/WordPress/Requests`.

  All links in code and documentation were updated accordingly.

  Note: the Composer package name remains unchanged ([`rmccue/requests`](https://packagist.org/packages/rmccue/requests)), as well as the documentation site ([requests.ryanmccue.info](https://requests.ryanmccue.info/)).

  (props [@dd32][gh-dd32], [@JustinyAhin][gh-JustinyAhin], [@jrfnl][gh-jrfnl], [@rmccue][gh-rmccue], [#440][gh-440], [#441][gh-441], [#448][gh-448])


- **Manage `"Expect"` header with `cURL` transport**

  By default, `cURL` adds a `Expect: 100-Continue` header to certain requests. This can add as much as a second delay to requests done using `cURL`. This is [discussed on the cURL mailing list](https://curl.se/mail/lib-2017-07/0013.html).

  To prevent this, `Requests` now adds an empty `"Expect"` header to requests that are smaller than 1 MB and use HTTP/1.1.

  (props [@carlalexander][gh-carlalexander], [@schlessera][gh-schlessera], [@TimothyBJacobs][gh-TimothyBJacobs], [#453][gh-453], [#454][gh-454], [#469][gh-469])


- **Update bundled certificates as of 2021-02-12**

  The bundled certificates were updated. A small subset of expired certificates are still included for legacy reasons (and support).

  (props [@ozh][gh-ozh], [@patmead][gh-patmead], [@schlessera][gh-schlessera], [@todeveni][gh-todeveni], [#385][gh-385], [#398][gh-398], [#451][gh-451])


- **Add required `Content-*` headers for empty `POST` requests**

  Sends the `Content-Length` and `Content-Type` headers even for empty `POST` requests, as the length is expected as per [RFC2616 Section 14.13](https://tools.ietf.org/html/rfc2616#section-14.13):
  ```
  Content-Length header "SHOULD" be included. In practice, it is not
  used for GET nor HEAD requests, but is expected for POST requests.
  ```

  (props [@dd32][gh-dd32], [@gstrauss][gh-gstrauss], [@jrfnl][gh-jrfnl], [@soulseekah][gh-soulseekah], [#248][gh-248], [#249][gh-249], [#318][gh-318], [#368][gh-368])


- **Ignore locale when creating the HTTP version string from a float**

  The previous behavior allowed for the locale to mess up the float to string conversion resulting in a `GET / HTTP/1,1` instead of `GET / HTTP/1.1` request.

  (props [@tonebender][gh-tonebender], [@Zegnat][gh-Zegnat], [#335][gh-335], [#339][gh-339])


- **Make `verify => false` work with `fsockopen`**

  This allows the `fsockopen` transport now to ignore SSL failures when requested.

  (props [@soulseekah][gh-soulseekah], [#310][gh-310], [#311][gh-311])


- **Only include port number in the `Host` header if it differs from the default**

  The code was not violating the RFC per se, but also not following standard practice of leaving the port off when it is the default port for the scheme, which could lead to connectivity issues.

  (props [@amandato][gh-amandato], [@dd32][gh-dd32], [#238][gh-238])


- **Fix PHP cross-version compatibility**

  Important fixes have been made to improve cross-version compatibility of the code across all supported PHP versions.

  - Use documented order for `implode()` arguments.
  - Harden type handling when no domain was passed.
  - Explicitly cast `$url` property to `string` in `Requests::parse_response()`.
  - Initialize `$body` property to an empty string in `Requests::parse_response()`.
  - Ensure the stream handle is valid before trying to close it.
  - Ensure the `$callback` in the `FilteredIterator` is callable before calling it.

  (props [@aaronjorbin][gh-aaronjorbin], [@jrfnl][gh-jrfnl], [#346][gh-346], [#370][gh-370], [#425][gh-425], [#426][gh-426], [#456][gh-456], [#457][gh-457])


- **Improve testing**

  Lots of improvements were made to render the tests more reliable and increase the coverage.

  And to top it all off, all tests are now run against all supported PHP versions, including PHP 8.0.

  (props [@datagutten][gh-datagutten], [@jrfnl][gh-jrfnl], [@schlessera][gh-schlessera], [#345][gh-345], [#351][gh-351], [#355][gh-355], [#366][gh-366], [#412][gh-412], [#414][gh-414], [#445][gh-445], [#458][gh-458], [#464][gh-464])


- **Improve code quality and style**

  A whole swoop of changes has been made to harden the code and make it more consistent.

  The code style has been made consistent across both code and tests and is now enforced via a custom PHPCS rule set.

  The WordPress Coding Standards were chosen as the basis for the code style checks as most contributors to this library originate from the WordPress community and will be familiar with this code style.

  Main differences from the WordPress Coding Standards based on discussions and an analysis of the code styles already in use:

  - No whitespace on the inside of parentheses.
  - No Yoda conditions.

  A more detailed overview of the decisions that went into the final code style rules can be found at [#434][gh-434].

  (props [@jrfnl][gh-jrfnl], [@KasperFranz][gh-KasperFranz], [@ozh][gh-ozh], [@schlessera][gh-schlessera], [@TysonAndre][gh-TysonAndre], [#263][gh-263], [#296][gh-296], [#328][gh-328], [#358][gh-358], [#359][gh-359], [#360][gh-360], [#361][gh-361], [#362][gh-362], [#363][gh-363], [#364][gh-364], [#386][gh-386], [#396][gh-396], [#399][gh-399], [#400][gh-400], [#401][gh-401], [#402][gh-402], [#403][gh-403], [#404][gh-404], [#405][gh-405], [#406][gh-406], [#408][gh-408], [#409][gh-409], [#410][gh-410], [#411][gh-411], [#413][gh-413], [#415][gh-415], [#416][gh-416], [#417][gh-417], [#423][gh-423], [#424][gh-424], [#434][gh-434])


- **Replace Travis CI with GitHub Actions (partial)**

  The entire CI setup is gradually being moved from Travis CI to GitHub Actions.

  At this point, GitHub Actions takes over the CI from PHP 5.5 onwards, leaving Travis CI as a fallback for lower PHP versions.

  This move will be completed after the planned minimum version bump to PHP 5.6+ with the next release, at which point we will get rid of all the remaining Travis CI integrations.

  (props [@dd32][gh-dd32], [@desrosj][gh-desrosj], [@jrfnl][gh-jrfnl], [@ntwb][gh-ntwb], [@ozh][gh-ozh], [@schlessera][gh-schlessera], [@TimothyBJacobs][gh-TimothyBJacobs], [@TysonAndre][gh-TysonAndre], [#280][gh-280], [#298][gh-298], [#302][gh-302], [#303][gh-303], [#352][gh-352], [#353][gh-353], [#354][gh-354], [#356][gh-356], [#388][gh-388], [#397][gh-397], [#428][gh-428], [#436][gh-436], [#439][gh-439], [#461][gh-461], [#467][gh-467])


- **Update and improve documentation**
  - Use clearer and more inclusive language.
  - Update the GitHub Pages site.
  - Update content and various tweaks to the markdown.
  - Fix code blocks in `README.md` file.
  - Add pagination to documentation pages.

  (props [@desrosj][gh-desrosj], [@jrfnl][gh-jrfnl], [@JustinyAhin][gh-JustinyAhin], [@tnorthcutt][gh-tnorthcutt], [#334][gh-334], [#367][gh-367], [#387][gh-387], [#443][gh-443], [#462][gh-462], [#465][gh-465], [#468][gh-468], [#471][gh-471] )

[gh-194]: https://github.com/WordPress/Requests/issues/194
[gh-238]: https://github.com/WordPress/Requests/issues/238
[gh-248]: https://github.com/WordPress/Requests/issues/248
[gh-249]: https://github.com/WordPress/Requests/issues/249
[gh-263]: https://github.com/WordPress/Requests/issues/263
[gh-280]: https://github.com/WordPress/Requests/issues/280
[gh-296]: https://github.com/WordPress/Requests/issues/296
[gh-298]: https://github.com/WordPress/Requests/issues/298
[gh-302]: https://github.com/WordPress/Requests/issues/302
[gh-303]: https://github.com/WordPress/Requests/issues/303
[gh-310]: https://github.com/WordPress/Requests/issues/310
[gh-311]: https://github.com/WordPress/Requests/issues/311
[gh-318]: https://github.com/WordPress/Requests/issues/318
[gh-328]: https://github.com/WordPress/Requests/issues/328
[gh-334]: https://github.com/WordPress/Requests/issues/334
[gh-335]: https://github.com/WordPress/Requests/issues/335
[gh-339]: https://github.com/WordPress/Requests/issues/339
[gh-345]: https://github.com/WordPress/Requests/issues/345
[gh-346]: https://github.com/WordPress/Requests/issues/346
[gh-351]: https://github.com/WordPress/Requests/issues/351
[gh-352]: https://github.com/WordPress/Requests/issues/352
[gh-353]: https://github.com/WordPress/Requests/issues/353
[gh-354]: https://github.com/WordPress/Requests/issues/354
[gh-355]: https://github.com/WordPress/Requests/issues/355
[gh-356]: https://github.com/WordPress/Requests/issues/356
[gh-358]: https://github.com/WordPress/Requests/issues/358
[gh-359]: https://github.com/WordPress/Requests/issues/359
[gh-360]: https://github.com/WordPress/Requests/issues/360
[gh-361]: https://github.com/WordPress/Requests/issues/361
[gh-362]: https://github.com/WordPress/Requests/issues/362
[gh-363]: https://github.com/WordPress/Requests/issues/363
[gh-364]: https://github.com/WordPress/Requests/issues/364
[gh-366]: https://github.com/WordPress/Requests/issues/366
[gh-367]: https://github.com/WordPress/Requests/issues/367
[gh-367]: https://github.com/WordPress/Requests/issues/367
[gh-368]: https://github.com/WordPress/Requests/issues/368
[gh-370]: https://github.com/WordPress/Requests/issues/370
[gh-385]: https://github.com/WordPress/Requests/issues/385
[gh-386]: https://github.com/WordPress/Requests/issues/386
[gh-387]: https://github.com/WordPress/Requests/issues/387
[gh-388]: https://github.com/WordPress/Requests/issues/388
[gh-396]: https://github.com/WordPress/Requests/issues/396
[gh-397]: https://github.com/WordPress/Requests/issues/397
[gh-398]: https://github.com/WordPress/Requests/issues/398
[gh-399]: https://github.com/WordPress/Requests/issues/399
[gh-400]: https://github.com/WordPress/Requests/issues/400
[gh-401]: https://github.com/WordPress/Requests/issues/401
[gh-402]: https://github.com/WordPress/Requests/issues/402
[gh-403]: https://github.com/WordPress/Requests/issues/403
[gh-404]: https://github.com/WordPress/Requests/issues/404
[gh-405]: https://github.com/WordPress/Requests/issues/405
[gh-406]: https://github.com/WordPress/Requests/issues/406
[gh-408]: https://github.com/WordPress/Requests/issues/408
[gh-409]: https://github.com/WordPress/Requests/issues/409
[gh-410]: https://github.com/WordPress/Requests/issues/410
[gh-411]: https://github.com/WordPress/Requests/issues/411
[gh-412]: https://github.com/WordPress/Requests/issues/412
[gh-413]: https://github.com/WordPress/Requests/issues/413
[gh-414]: https://github.com/WordPress/Requests/issues/414
[gh-415]: https://github.com/WordPress/Requests/issues/415
[gh-416]: https://github.com/WordPress/Requests/issues/416
[gh-417]: https://github.com/WordPress/Requests/issues/417
[gh-421]: https://github.com/WordPress/Requests/issues/421
[gh-422]: https://github.com/WordPress/Requests/issues/422
[gh-423]: https://github.com/WordPress/Requests/issues/423
[gh-424]: https://github.com/WordPress/Requests/issues/424
[gh-425]: https://github.com/WordPress/Requests/issues/425
[gh-426]: https://github.com/WordPress/Requests/issues/426
[gh-428]: https://github.com/WordPress/Requests/issues/428
[gh-434]: https://github.com/WordPress/Requests/issues/434
[gh-436]: https://github.com/WordPress/Requests/issues/436
[gh-439]: https://github.com/WordPress/Requests/issues/439
[gh-440]: https://github.com/WordPress/Requests/issues/440
[gh-441]: https://github.com/WordPress/Requests/issues/441
[gh-443]: https://github.com/WordPress/Requests/issues/443
[gh-445]: https://github.com/WordPress/Requests/issues/445
[gh-448]: https://github.com/WordPress/Requests/issues/448
[gh-451]: https://github.com/WordPress/Requests/issues/451
[gh-453]: https://github.com/WordPress/Requests/issues/453
[gh-454]: https://github.com/WordPress/Requests/issues/454
[gh-456]: https://github.com/WordPress/Requests/issues/456
[gh-457]: https://github.com/WordPress/Requests/issues/457
[gh-458]: https://github.com/WordPress/Requests/issues/458
[gh-461]: https://github.com/WordPress/Requests/issues/461
[gh-462]: https://github.com/WordPress/Requests/issues/462
[gh-464]: https://github.com/WordPress/Requests/issues/464
[gh-465]: https://github.com/WordPress/Requests/issues/465
[gh-467]: https://github.com/WordPress/Requests/issues/467
[gh-468]: https://github.com/WordPress/Requests/issues/468
[gh-469]: https://github.com/WordPress/Requests/issues/469
[gh-471]: https://github.com/WordPress/Requests/issues/471

1.7.0
-----

- Add support for HHVM and PHP 7

  Requests is now tested against both HHVM and PHP 7, and they are supported as
  first-party platforms.

  (props [@rmccue][gh-rmccue], [#106][gh-106], [#176][gh-176])

- Transfer & connect timeouts, in seconds & milliseconds

  cURL is unable to handle timeouts under a second in DNS lookups, so we round
  those up to ensure 1-999ms isn't counted as an instant failure.

  (props [@ozh][gh-ozh], [@rmccue][gh-rmccue], [#97][gh-97], [#216][gh-216])

- Rework cookie handling to be more thorough.

  Cookies are now restricted to the same-origin by default, expiration is checked.

  (props [@catharsisjelly][gh-catharsisjelly], [@rmccue][gh-rmccue], [#120][gh-120], [#124][gh-124], [#130][gh-130], [#132][gh-132], [#156][gh-156])

- Improve testing

  Tests are now run locally to speed them up, as well as further general
  improvements to the quality of the testing suite. There are now also
  comprehensive proxy tests to ensure coverage there.

  (props [@rmccue][gh-rmccue], [#75][gh-75], [#107][gh-107], [#170][gh-170], [#177][gh-177], [#181][gh-181], [#183][gh-183], [#185][gh-185], [#196][gh-196], [#202][gh-202], [#203][gh-203])

- Support custom HTTP methods

  Previously, custom HTTP methods were only supported on sockets; they are now
  supported across all transports.

  (props [@ocean90][gh-ocean90], [#227][gh-227])

- Add byte limit option

  (props [@rmccue][gh-rmccue], [#172][gh-172])

- Support a Requests_Proxy_HTTP() instance for the proxy setting.

  (props [@ocean90][gh-ocean90], [#223][gh-223])

- Add progress hook

  (props [@rmccue][gh-rmccue], [#180][gh-180])

- Add a before_redirect hook to alter redirects

  (props [@rmccue][gh-rmccue], [#205][gh-205])

- Pass cURL info to after_request

  (props [@rmccue][gh-rmccue], [#206][gh-206])

- Remove explicit autoload in Composer installation instructions

  (props [@SlikNL][gh-SlikNL], [#86][gh-86])

- Restrict CURLOPT_PROTOCOLS on `defined()` instead of `version_compare()`

  (props [@ozh][gh-ozh], [#92][gh-92])

- Fix doc - typo in "Authentication"

  (props [@remik][gh-remik], [#99][gh-99])

- Contextually check for a valid transport

  (props [@ozh][gh-ozh], [#101][gh-101])

- Follow relative redirects correctly

  (props [@ozh][gh-ozh], [#103][gh-103])

- Use cURL's version_number

  (props [@mishan][gh-mishan], [#104][gh-104])

- Removed duplicated option docs

  (props [@staabm][gh-staabm], [#112][gh-112])

- code styling fixed

  (props [@imsaintx][gh-imsaintx], [#113][gh-113])

- Fix IRI "normalization"

  (props [@ozh][gh-ozh], [#128][gh-128])

- Mention two PHP extension dependencies in the README.

  (props [@orlitzky][gh-orlitzky], [#136][gh-136])

- Ignore coverage report files

  (props [@ozh][gh-ozh], [#148][gh-148])

- drop obsolete "return" after throw

  (props [@staabm][gh-staabm], [#150][gh-150])

- Updated exception message to specify both http + https

  (props [@beutnagel][gh-beutnagel], [#162][gh-162])

- Sets `stream_headers` method to public to allow calling it from other
places.

  (props [@adri][gh-adri], [#158][gh-158])

- Remove duplicated stream_get_meta_data call

  (props [@rmccue][gh-rmccue], [#179][gh-179])

- Transmits $errno from stream_socket_client in exception

  (props [@laurentmartelli][gh-laurentmartelli], [#174][gh-174])

- Correct methods to use snake_case

  (props [@rmccue][gh-rmccue], [#184][gh-184])

- Improve code quality

  (props [@rmccue][gh-rmccue], [#186][gh-186])

- Update Build Status image

  (props [@rmccue][gh-rmccue], [#187][gh-187])

- Fix/Rationalize transports (v2)

  (props [@rmccue][gh-rmccue], [#188][gh-188])

- Surface cURL errors

  (props [@ifwe][gh-ifwe], [#194][gh-194])

- Fix for memleak and curl_close() never being called

  (props [@kwuerl][gh-kwuerl], [#200][gh-200])

- addex how to install with composer

  (props [@royopa][gh-royopa], [#164][gh-164])

- Uppercase the method to ensure compatibility

  (props [@rmccue][gh-rmccue], [#207][gh-207])

- Store default certificate path

  (props [@rmccue][gh-rmccue], [#210][gh-210])

- Force closing keep-alive connections on old cURL

  (props [@rmccue][gh-rmccue], [#211][gh-211])

- Docs: Updated HTTP links with HTTPS links where applicable

  (props [@ntwb][gh-ntwb], [#215][gh-215])

- Remove the executable bit

  (props [@ocean90][gh-ocean90], [#224][gh-224])

- Change more links to HTTPS

  (props [@rmccue][gh-rmccue], [#217][gh-217])

- Bail from cURL when either `curl_init()` OR `curl_exec()` are unavailable

  (props [@dd32][gh-dd32], [#230][gh-230])

- Disable OpenSSL's internal peer_name checking when `verifyname` is disabled.

  (props [@dd32][gh-dd32], [#239][gh-239])

- Only include the port number in the `Host` header when it differs from
default

  (props [@dd32][gh-dd32], [#238][gh-238])

- Respect port if specified for HTTPS connections

  (props [@dd32][gh-dd32], [#237][gh-237])

- Allow paths starting with a double-slash

  (props [@rmccue][gh-rmccue], [#240][gh-240])

- Fixes bug in rfc2616 #3.6.1 implementation.

  (props [@stephenharris][gh-stephenharris], [#236][gh-236], [#3][gh-3])

- CURLOPT_HTTPHEADER在php7接受空数组导致php-fpm奔溃

  (props [@qibinghua][gh-qibinghua], [#219][gh-219])

[gh-3]: https://github.com/WordPress/Requests/issues/3
[gh-75]: https://github.com/WordPress/Requests/issues/75
[gh-86]: https://github.com/WordPress/Requests/issues/86
[gh-92]: https://github.com/WordPress/Requests/issues/92
[gh-97]: https://github.com/WordPress/Requests/issues/97
[gh-99]: https://github.com/WordPress/Requests/issues/99
[gh-101]: https://github.com/WordPress/Requests/issues/101
[gh-103]: https://github.com/WordPress/Requests/issues/103
[gh-104]: https://github.com/WordPress/Requests/issues/104
[gh-106]: https://github.com/WordPress/Requests/issues/106
[gh-107]: https://github.com/WordPress/Requests/issues/107
[gh-112]: https://github.com/WordPress/Requests/issues/112
[gh-113]: https://github.com/WordPress/Requests/issues/113
[gh-120]: https://github.com/WordPress/Requests/issues/120
[gh-124]: https://github.com/WordPress/Requests/issues/124
[gh-128]: https://github.com/WordPress/Requests/issues/128
[gh-130]: https://github.com/WordPress/Requests/issues/130
[gh-132]: https://github.com/WordPress/Requests/issues/132
[gh-136]: https://github.com/WordPress/Requests/issues/136
[gh-148]: https://github.com/WordPress/Requests/issues/148
[gh-150]: https://github.com/WordPress/Requests/issues/150
[gh-156]: https://github.com/WordPress/Requests/issues/156
[gh-158]: https://github.com/WordPress/Requests/issues/158
[gh-162]: https://github.com/WordPress/Requests/issues/162
[gh-164]: https://github.com/WordPress/Requests/issues/164
[gh-170]: https://github.com/WordPress/Requests/issues/170
[gh-172]: https://github.com/WordPress/Requests/issues/172
[gh-174]: https://github.com/WordPress/Requests/issues/174
[gh-176]: https://github.com/WordPress/Requests/issues/176
[gh-177]: https://github.com/WordPress/Requests/issues/177
[gh-179]: https://github.com/WordPress/Requests/issues/179
[gh-180]: https://github.com/WordPress/Requests/issues/180
[gh-181]: https://github.com/WordPress/Requests/issues/181
[gh-183]: https://github.com/WordPress/Requests/issues/183
[gh-184]: https://github.com/WordPress/Requests/issues/184
[gh-185]: https://github.com/WordPress/Requests/issues/185
[gh-186]: https://github.com/WordPress/Requests/issues/186
[gh-187]: https://github.com/WordPress/Requests/issues/187
[gh-188]: https://github.com/WordPress/Requests/issues/188
[gh-194]: https://github.com/WordPress/Requests/issues/194
[gh-196]: https://github.com/WordPress/Requests/issues/196
[gh-200]: https://github.com/WordPress/Requests/issues/200
[gh-202]: https://github.com/WordPress/Requests/issues/202
[gh-203]: https://github.com/WordPress/Requests/issues/203
[gh-205]: https://github.com/WordPress/Requests/issues/205
[gh-206]: https://github.com/WordPress/Requests/issues/206
[gh-207]: https://github.com/WordPress/Requests/issues/207
[gh-210]: https://github.com/WordPress/Requests/issues/210
[gh-211]: https://github.com/WordPress/Requests/issues/211
[gh-215]: https://github.com/WordPress/Requests/issues/215
[gh-216]: https://github.com/WordPress/Requests/issues/216
[gh-217]: https://github.com/WordPress/Requests/issues/217
[gh-219]: https://github.com/WordPress/Requests/issues/219
[gh-223]: https://github.com/WordPress/Requests/issues/223
[gh-224]: https://github.com/WordPress/Requests/issues/224
[gh-227]: https://github.com/WordPress/Requests/issues/227
[gh-230]: https://github.com/WordPress/Requests/issues/230
[gh-236]: https://github.com/WordPress/Requests/issues/236
[gh-237]: https://github.com/WordPress/Requests/issues/237
[gh-238]: https://github.com/WordPress/Requests/issues/238
[gh-239]: https://github.com/WordPress/Requests/issues/239
[gh-240]: https://github.com/WordPress/Requests/issues/240

1.6.0
-----
- [Add multiple request support][#23] - Send multiple HTTP requests with both
  fsockopen and cURL, transparently falling back to synchronous when
  not supported.

- [Add proxy support][#70] - HTTP proxies are now natively supported via a
  [high-level API][docs/proxy]. Major props to Ozh for his fantastic work
  on this.

- [Verify host name for SSL requests][#63] - Requests is now the first and only
  standalone HTTP library to fully verify SSL hostnames even with socket
  connections. Thanks to Michael Adams, Dion Hulse, Jon Cave, and Pádraic Brady
  for reviewing the crucial code behind this.

- [Add cookie support][#64] - Adds built-in support for cookies (built entirely
  as a high-level API)

- [Add sessions][#62] - To compliment cookies, [sessions][docs/usage-advanced]
  can be created with a base URL and default options, plus a shared cookie jar.

- Add [PUT][#1], [DELETE][#3], and [PATCH][#2] request support

- [Add Composer support][#6] - You can now install Requests via the
  `rmccue/requests` package on Composer

[docs/proxy]: https://requests.ryanmccue.info/docs/proxy.html
[docs/usage-advanced]: https://requests.ryanmccue.info/docs/usage-advanced.html

[#1]: https://github.com/WordPress/Requests/issues/1
[#2]: https://github.com/WordPress/Requests/issues/2
[#3]: https://github.com/WordPress/Requests/issues/3
[#6]: https://github.com/WordPress/Requests/issues/6
[#9]: https://github.com/WordPress/Requests/issues/9
[#23]: https://github.com/WordPress/Requests/issues/23
[#62]: https://github.com/WordPress/Requests/issues/62
[#63]: https://github.com/WordPress/Requests/issues/63
[#64]: https://github.com/WordPress/Requests/issues/64
[#70]: https://github.com/WordPress/Requests/issues/70

[View all changes][https://github.com/WordPress/Requests/compare/v1.5.0...v1.6.0]

1.5.0
-----
Initial release!

[gh-aaronjorbin]: https://github.com/aaronjorbin
[gh-adri]: https://github.com/adri
[gh-alpipego]: https://github.com/alpipego/
[gh-amandato]: https://github.com/amandato
[gh-ayesh]: https://github.com/Ayesh
[gh-beutnagel]: https://github.com/beutnagel
[gh-carlalexander]: https://github.com/carlalexander
[gh-catharsisjelly]: https://github.com/catharsisjelly
[gh-ccrims0n]: https://github.com/ccrims0n
[gh-costdev]: https://github.com/costdev
[gh-datagutten]: https://github.com/datagutten
[gh-dustinrue]: https://github.com/dustinrue
[gh-dd32]: https://github.com/dd32
[gh-desrosj]: https://github.com/desrosj
[gh-gstrauss]: https://github.com/gstrauss
[gh-ifwe]: https://github.com/ifwe
[gh-imsaintx]: https://github.com/imsaintx
[gh-jegrandet]: https://github.com/jegrandet
[gh-JustinyAhin]: https://github.com/JustinyAhin
[gh-jrfnl]: https://github.com/jrfnl
[gh-KasperFranz]: https://github.com/KasperFranz
[gh-kwuerl]: https://github.com/kwuerl
[gh-laszlof]: https://github.com/laszlof
[gh-laurentmartelli]: https://github.com/laurentmartelli
[gh-mbabker]: https://github.com/mbabker
[gh-mircobabini]: https://github.com/mircobabini
[gh-mishan]: https://github.com/mishan
[gh-ntwb]: https://github.com/ntwb
[gh-ocean90]: https://github.com/ocean90
[gh-orlitzky]: https://github.com/orlitzky
[gh-ozh]: https://github.com/ozh
[gh-patmead]: https://github.com/patmead
[gh-peterwilsoncc]: https://github.com/peterwilsoncc
[gh-qibinghua]: https://github.com/qibinghua
[gh-remik]: https://github.com/remik
[gh-rmccue]: https://github.com/rmccue
[gh-royopa]: https://github.com/royopa
[gh-schlessera]: https://github.com/schlessera
[gh-SergeyBiryukov]: https://github.com/SergeyBiryukov
[gh-SlikNL]: https://github.com/SlikNL
[gh-soulseekah]: https://github.com/soulseekah
[gh-staabm]: https://github.com/staabm
[gh-stephenharris]: https://github.com/stephenharris
[gh-szepeviktor]: https://github.com/szepeviktor
[gh-TimothyBJacobs]: https://github.com/TimothyBJacobs
[gh-tnorthcutt]: https://github.com/tnorthcutt
[gh-todeveni]: https://github.com/todeveni
[gh-tomsommer]: https://github.com/tomsommer
[gh-tonebender]: https://github.com/tonebender
[gh-twdnhfr]: https://github.com/twdnhfr
[gh-TysonAndre]: https://github.com/TysonAndre
[gh-whyisjake]: https://github.com/whyisjake
[gh-wojsmol]: https://github.com/wojsmol
[gh-xknown]: https://github.com/xknown
[gh-Zegnat]: https://github.com/Zegnat
[gh-ZsgsDesign]: https://github.com/ZsgsDesign
