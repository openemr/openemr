<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * sr_RS-Revision: 28.October.2015
 */
return array(
    // Zend\Authentication\Validator\Authentication
    "Invalid identity" => "Погрешан идентитет",
    "Identity is ambiguous" => "Идентитет је двосмислен",
    "Invalid password" => "Погрешна лозинка",
    "Authentication failed" => "Неуспела аутентификација",

    // Zend\I18n\Validator\Alnum
    "Invalid type given. String, integer or float expected" => "Унет је погрешан тип. Очекиван је String, integer или float",
    "The input contains characters which are non alphabetic and no digits" => "Унос садржи карактере који нису алфабетски и нису бројеви",
    "The input is an empty string" => "Унет је празан string",

    // Zend\I18n\Validator\Alpha
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input contains non alphabetic characters" => "Унос садржи не-алфабетске карактере",
    "The input is an empty string" => "Унет је празан string",

    // Zend\I18n\Validator\DateTime
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input does not appear to be a valid datetime" => "Унет је неисправан datetime",

    // Zend\I18n\Validator\IsFloat
    "Invalid type given. String, integer or float expected" => "Унет је погрешан тип. Очекиван је String, integer или float",
    "The input does not appear to be a float" => "Унос није стварни број",

    // Zend\I18n\Validator\IsInt
    "Invalid type given. String or integer expected" => "Унет је погрешан тип. Очекиван је String или integer",
    "The input does not appear to be an integer" => "Унос није цео број",

    // Zend\I18n\Validator\PhoneNumber
    "The input does not match a phone number format" => "Унос не одговара формату телефонског броја",
    "The country provided is currently unsupported" => "Одабрана земља тренутно није подржана",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",

    // Zend\I18n\Validator\PostCode
    "Invalid type given. String or integer expected" => "Унет је погрешан тип. Очекиван је String или integer",
    "The input does not appear to be a postal code" => "Унос није поштански број",
    "An exception has been raised while validating the input" => "Дошло је до грешке приликом провере уноса",

    // Zend\Validator\Barcode
    "The input failed checksum validation" => "Унос није прошао проверу вредности",
    "The input contains invalid characters" => "Унос садржи неисправне карактере",
    "The input should have a length of %length% characters" => "Дозвољени број карактера је %length%",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",

    // Zend\Validator\Between
    "The input is not between '%min%' and '%max%', inclusively" => "Унос није искључиво између '%min%' и '%max%",
    "The input is not strictly between '%min%' and '%max%'" => "Унос није стриктно између '%min%' и '%max%'",

    // Zend\Validator\Bitwise
    "The input has no common bit set with '%control%'" => "Унос нема подразумевани бит подешен са '%control%'",
    "The input doesn't have the same bits set as '%control%'" => "Унос нема исте бите подешене као '%control%'",
    "The input has common bit set with '%control%'" => "Унос има подразумевани бит подешен са '%control%'",

    // Zend\Validator\Callback
    "The input is not valid" => "Унос је неисправан",
    "An exception has been raised within the callback" => "Дошло је до грешке приликом извршавања 'callback' функције",

    // Zend\Validator\CreditCard
    "The input seems to contain an invalid checksum" => "Унос садржи неисправну вредност",
    "The input must contain only digits" => "Унос мора да садржи само бројеве",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input contains an invalid amount of digits" => "Унос садржи недозвољен број цифара",
    "The input is not from an allowed institute" => "Унос није од овлашћеног института",
    "The input seems to be an invalid credit card number" => "Унет је неисправан број кредитне картице",
    "An exception has been raised while validating the input" => "Дошло је до грешке приликом провере уноса",

    // Zend\Validator\Csrf
    "The form submitted did not originate from the expected site" => "Послати подаци не потичу са очекиваног сајта",

    // Zend\Validator\Date
    "Invalid type given. String, integer, array or DateTime expected" => "Унет је погрешан тип. Очекиван је String, integer, array или DateTime",
    "The input does not appear to be a valid date" => "Унет је неисправан датум",
    "The input does not fit the date format '%format%'" => "Унос не одговара формату датума '%format%'",

    // Zend\Validator\DateStep
    "Invalid type given. String, integer, array or DateTime expected" => "Унет је погрешан тип. Очекиван је String, integer, array или DateTime",
    "The input does not appear to be a valid date" => "Унет је неисправан датум",
    "The input does not fit the date format '%format%'" => "Унос не одговара формату датума '%format%'",
    "The input is not a valid step" => "Унос није исправан корак",

    // Zend\Validator\Db\AbstractDb
    "No record matching the input was found" => "Није пронађен запис који одговара уносу",
    "A record matching the input was found" => "Пронађен је запис који одговара уносу",

    // Zend\Validator\Digits
    "The input must contain only digits" => "Унос мора да садржи само бројеве",
    "The input is an empty string" => "Унет је празан string",
    "Invalid type given. String, integer or float expected" => "Унет је погрешан тип. Очекиван је String, integer или float",

    // Zend\Validator\EmailAddress
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input is not a valid email address. Use the basic format local-part@hostname" => "Унета је неисправна адреса е-поште. Користите основни формат local-part@hostname",
    "'%hostname%' is not a valid hostname for the email address" => "'%hostname%' није исправан hostname за адресу е-поште",
    "'%hostname%' does not appear to have any valid MX or A records for the email address" => "'%hostname%' нема исправних MX или A записа за адресу е-поште",
    "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network" => "'%hostname%' није у рутабилном мрежном сегменту. Адреса е-поште не би требало да се одређује са јавне мреже",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' се не може упарити у dot-atom формату",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' се не може упарити у quoted-string формату",
    "'%localPart%' is not a valid local part for the email address" => "'%localPart%' није исправан локални део за адресу е-поште",
    "The input exceeds the allowed length" => "Унос превазилази дозвољену дужину",

    // Zend\Validator\Explode
    "Invalid type given" => "Унет је погрешан тип",

    // Zend\Validator\File\Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Превише фајлова, дозвољени максимум је '%max%' а унет је '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Премало фајлова, очекивани минимум је '%min%' а унет је '%count%'",

    // Zend\Validator\File\Crc32
    "File does not match the given crc32 hashes" => "Фајл не одговара датим crc32 вредностима",
    "A crc32 hash could not be evaluated for the given file" => "crc32 вредност се не може одредити за дати фајл",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\ExcludeExtension
    "File has an incorrect extension" => "Фајл има неисправну екстензију",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\Exists
    "File does not exist" => "Фајл не постоји",

    // Zend\Validator\File\Extension
    "File has an incorrect extension" => "Фајл има неисправну екстензију",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Сви фајлови збирно треба да имају максималну величину од '%max%' а детектована величина је '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Сви фајлови збирно треба да имају минималну величину од '%min%' а детектована величина је '%size%'",
    "One or more files can not be read" => "Један или више фајлова су нечитљиви",

    // Zend\Validator\File\Hash
    "File does not match the given hashes" => "Фајл не одговара датим hash вредностима",
    "A hash could not be evaluated for the given file" => "Hash вредност се не може одредити за дати фајл",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\ImageSize
    "Maximum allowed width for image should be '%maxwidth%' but '%width%' detected" => "Максимално дозвољена ширина слике треба да буде '%maxwidth%' али детектована је '%width%'",
    "Minimum expected width for image should be '%minwidth%' but '%width%' detected" => "Минимално очекивана ширина слике треба да буде '%minwidth%' али детектована је '%width%'",
    "Maximum allowed height for image should be '%maxheight%' but '%height%' detected" => "Максимално дозвољена висина слике треба да буде '%maxheight%' али детектована је '%height%'",
    "Minimum expected height for image should be '%minheight%' but '%height%' detected" => "Минимално очекивана висина слике треба да буде '%minheight%' али детектована је '%height%'",
    "The size of image could not be detected" => "Величина слике не може бити детектована",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\IsCompressed
    "File is not compressed, '%type%' detected" => "Фајл није компресован, '%type%' детектован",
    "The mimetype could not be detected from the file" => "Мimеtype фајла не може бити детектован",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\IsImage
    "File is no image, '%type%' detected" => "Фајл није слика, '%type%' детектован",
    "The mimetype could not be detected from the file" => "Мimеtype фајла не може бити детектован",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\Md5
    "File does not match the given md5 hashes" => "Фајл не одговара датим md5 вредностима",
    "An md5 hash could not be evaluated for the given file" => "md5 вредност се не може одредити за дати фајл",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\MimeType
    "File has an incorrect mimetype of '%type%'" => "Фајл има нетачан mimetype '%type%'",
    "The mimetype could not be detected from the file" => "Мimеtype фајла не може бити детектован",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\NotExists
    "File exists" => "Фајл постоји",

    // Zend\Validator\File\Sha1
    "File does not match the given sha1 hashes" => "Фајл не одговара датим sha1 вредностима",
    "A sha1 hash could not be evaluated for the given file" => "sha1 вредност се не може одредити за дати фајл",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\Size
    "Maximum allowed size for file is '%max%' but '%size%' detected" => "Максимално дозвољена величина фајла је '%max%' а детектована је '%size%'",
    "Minimum expected size for file is '%min%' but '%size%' detected" => "Минимално очекивана величина фајла је '%min%' а детектована је '%size%'",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\File\Upload
    "File '%value%' exceeds the defined ini size" => "Фајл '%value%' превазилази дефинисану ini величину",
    "File '%value%' exceeds the defined form size" => "Фајл '%value%' превазилази дефинисану form величину",
    "File '%value%' was only partially uploaded" => "Фајл '%value%' је само делимично учитан",
    "File '%value%' was not uploaded" => "Фајл '%value%' није учитан",
    "No temporary directory was found for file '%value%'" => "Није пронађен привремени директоријум за фајл '%value%'",
    "File '%value%' can't be written" => "Фајл '%value%' не може бити написан",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP екстензија је дала грешку приликом учитавања фајла '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Фајл '%value%' је илегално учитан. Ово је могући напад",
    "File '%value%' was not found" => "Фајл '%value%' није пронађен",
    "Unknown error while uploading file '%value%'" => "Непозната грешка приликом учитавања фајла '%value%'",

    // Zend\Validator\File\UploadFile
    "File exceeds the defined ini size" => "Фајл превазилази дефинисану ini величину",
    "File exceeds the defined form size" => "Фајл превазилази дефинисану form величину",
    "File was only partially uploaded" => "Фајл је само делимично учитан",
    "File was not uploaded" => "Фајл није учитан",
    "No temporary directory was found for file" => "Није пронађен привремени директоријум за фајл",
    "File can't be written" => "Фајл не може бити написан",
    "A PHP extension returned an error while uploading the file" => "PHP екстензија је дала грешку приликом учитавања фајла",
    "File was illegally uploaded. This could be a possible attack" => "Фајл је илегално учитан. Ово је могући напад",
    "File was not found" => "Фајл није пронађен",
    "Unknown error while uploading file" => "Непозната грешка приликом учитавања фајла",

    // Zend\Validator\File\WordCount
    "Too many words, maximum '%max%' are allowed but '%count%' were counted" => "Превише речи, максимално дозвољено је '%max%' а избројано је '%count%'",
    "Too few words, minimum '%min%' are expected but '%count%' were counted" => "Премало речи, минимално очекивано је '%min%' а избројано је '%count%'",
    "File is not readable or does not exist" => "Фајл не може бити прочитан или не постоји",

    // Zend\Validator\GreaterThan
    "The input is not greater than '%min%'" => "Унос је већи од '%min%'",
    "The input is not greater or equal than '%min%'" => "Унос није већи или једнак од '%min%'",

    // Zend\Validator\Hex
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input contains non-hexadecimal characters" => "Унос садржи не-хексадецималне карактере",

    // Zend\Validator\Hostname
    "The input appears to be a DNS hostname but the given punycode notation cannot be decoded" => "Унос је DNS hostname али дату punycode нотацију је немогуће декодирати",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input appears to be a DNS hostname but contains a dash in an invalid position" => "Унос је DNS hostname али садржи цртицу на погрешној позицији",
    "The input does not match the expected structure for a DNS hostname" => "Унос не одговара очекиваној структури за DNS hostname",
    "The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "Унос је DNS hostname али не одговара шеми за TLD '%tld%'",
    "The input does not appear to be a valid local network name" => "Унет је неисправан назив локалне мреже",
    "The input does not appear to be a valid URI hostname" => "Унет је неисправан URI hostname",
    "The input appears to be an IP address, but IP addresses are not allowed" => "Унос је IP адреса, али IP адресе нису дозвољене",
    "The input appears to be a local network name but local network names are not allowed" => "Унос је назив локалне мреже али називи локалних мрежа нису дозвољени",
    "The input appears to be a DNS hostname but cannot extract TLD part" => "Унос је DNS hostname али није могуће одвојити TLD део",
    "The input appears to be a DNS hostname but cannot match TLD against known list" => "Унос је DNS hostname али не одговара познатој TLD листи",

    // Zend\Validator\Iban
    "Unknown country within the IBAN" => "Непозната земља унутар IBAN-а",
    "Countries outside the Single Euro Payments Area (SEPA) are not supported" => "Земље изван јединственог подручја плаћања у еврима (SEPA) нису подржане",
    "The input has a false IBAN format" => "Унос има погрешан IBAN формат",
    "The input has failed the IBAN check" => "Унос није прошао IBAN проверу",

    // Zend\Validator\Identical
    "The two given tokens do not match" => "Два унета токена се не поклапају",
    "No token was provided to match against" => "Токен за проверу није унет",

    // Zend\Validator\InArray
    "The input was not found in the haystack" => "Унос није пронађен у захтеваном низу вредности",

    // Zend\Validator\Ip
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input does not appear to be a valid IP address" => "Унета је неисправна IP адреса",

    // Zend\Validator\IsInstanceOf
    "The input is not an instance of '%className%'" => "Унос није инстанца класе '%className%'",

    // Zend\Validator\Isbn
    "Invalid type given. String or integer expected" => "Унет је погрешан тип. Очекиван је String или integer",
    "The input is not a valid ISBN number" => "Унет је неисправан ISBN број",

    // Zend\Validator\LessThan
    "The input is not less than '%max%'" => "Унос није мањи од '%max%'",
    "The input is not less or equal than '%max%'" => "Унос није мањи или једнак од '%max%'",

    // Zend\Validator\NotEmpty
    "Value is required and can't be empty" => "Вредност је обавезна и не може бити празна",
    "Invalid type given. String, integer, float, boolean or array expected" => "Унет је погрешан тип. Очекиван је String, integer, float, boolean или array",

    // Zend\Validator\Regex
    "Invalid type given. String, integer or float expected" => "Унет је погрешан тип. Очекиван је String, integer или float",
    "The input does not match against pattern '%pattern%'" => "Унос не одговара шаблону '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Дошло је до интерне грешке употребом шаблона '%pattern%'",

    // Zend\Validator\Sitemap\Changefreq
    "The input is not a valid sitemap changefreq" => "Унет је неисправан sitemap changefreq",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",

    // Zend\Validator\Sitemap\Lastmod
    "The input is not a valid sitemap lastmod" => "Унет је неисправан sitemap lastmod",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",

    // Zend\Validator\Sitemap\Loc
    "The input is not a valid sitemap location" => "Унет је неисправан sitemap location",
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",

    // Zend\Validator\Sitemap\Priority
    "The input is not a valid sitemap priority" => "Унет је неисправан sitemap priority",
    "Invalid type given. Numeric string, integer or float expected" => "Унет је погрешан тип. Очекиван је нумерички, цео број или стварни број",

    // Zend\Validator\Step
    "Invalid value given. Scalar expected" => "Погрешна вредност унета. Очекивана је скаларна",
    "The input is not a valid step" => "Унос није исправан корак",

    // Zend\Validator\StringLength
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input is less than %min% characters long" => "Унос је мањи од %min% карактера",
    "The input is more than %max% characters long" => "Унос је већи од %max% карактера",

    // Zend\Validator\Timezone
    "Invalid timezone given." => "Унета је неисправна временска зона.",
    "Invalid timezone location given." => "Унета је неисправна локација временске зоне.",
    "Invalid timezone abbreviation given." => "Унета је неисправна скраћеница временске зоне.",

    // Zend\Validator\Uri
    "Invalid type given. String expected" => "Унет је погрешан тип. Очекиван је String",
    "The input does not appear to be a valid Uri" => "Унет је неисправан Uri",
);
