<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * RU-Revision: 08.Apr.2015
 */
return [
    // Zend\I18n\Validator\Alnum
    "Invalid type given. String, integer or float expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом или числом с плавающей точкой",
    "The input contains characters which are non alphabetic and no digits" => "Значение содержит недопустимые символы. Разрешены только буквенные символы и цифры",
    "The input is an empty string" => "Значение не может быть пустой строкой",

    // Zend\I18n\Validator\Alpha
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input contains non alphabetic characters" => "Значение должно содержать только буквенные символы",
    "The input is an empty string" => "Значение не может быть пустой строкой",

    // Zend\I18n\Validator\DateTime
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input does not appear to be a valid datetime" => "Неправильное значение даты/времени",

    // Zend\I18n\Validator\Float
    "Invalid type given. String, integer or float expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом или числом с плавающей точкой",
    "The input does not appear to be a float" => "Значение не является числом с плавающей точкой",

    // Zend\I18n\Validator\Int
    "Invalid type given. String or integer expected" => "Недопустимый тип данных. Значение должно быть строкой или целым числом",
    "The input does not appear to be an integer" => "Значение не является целым числом",

    // Zend\I18n\Validator\PhoneNumber
    "The input does not match a phone number format" => "Значение не соответствует формату номера телефона",
    "The country provided is currently unsupported" => "Указанная страна в настоящее время не поддерживается",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",

    // Zend\I18n\Validator\PostCode
    "Invalid type given. String or integer expected" => "Недопустимый тип данных. Значение должно быть строкой или целым числом",
    "The input does not appear to be a postal code" => "Неправильное значение почтового кода",
    "An exception has been raised while validating the input" => "Произошла ошибка во время проверки значения почтового кода",

    // Zend\Validator\Barcode
    "The input failed checksum validation" => "Ошибка проверки контрольной суммы",
    "The input contains invalid characters" => "Значение содержит недопустимые символы",
    "The input should have a length of %length% characters" => "Длина значения поля должна составлять %length% символов",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",

    // Zend\Validator\Between
    "The input is not between '%min%' and '%max%', inclusively" => "Значение находится вне диапазона от '%min%' до '%max%', включительно",
    "The input is not strictly between '%min%' and '%max%'" => "Значение находится вне диапазона от '%min%' до '%max%'",

    // Zend\Validator\Bitwise
    "The input has no common bit set with '%control%'" => "The input has no common bit set with '%control%'",
    "The input doesn't have the same bits set as '%control%'" => "The input doesn't have the same bits set as '%control%'",
    "The input has common bit set with '%control%'" => "The input has common bit set with '%control%'",

    // Zend\Validator\Callback
    "The input is not valid" => "Недопустимое значение",
    "An exception has been raised within the callback" => "Произошла ошибка во время обратного вызова",

    // Zend\Validator\CreditCard
    "The input seems to contain an invalid checksum" => "Ошибка вычисления контрольной суммы",
    "The input must contain only digits" => "Значение должно содержать только цифры",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input contains an invalid amount of digits" => "Значение содержит недопустимое количество цифр",
    "The input is not from an allowed institute" => "Значение не входит в список разрешенных платежных систем",
    "The input seems to be an invalid credit card number" => "Неверный номер кредитной карточки",
    "An exception has been raised while validating the input" => "Произошла ошибка во время проверки значения номера кредитной карточки",

    // Zend\Validator\Csrf
    "The form submitted did not originate from the expected site" => "Время действия формы истекло или отправленная форма не принадлежит данному сайту. Попробуйте повторить операцию еще раз",

    // Zend\Validator\Date
    "Invalid type given. String, integer, array or DateTime expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом, массивом или объектом DateTime",
    "The input does not appear to be a valid date" => "Значение не является корректной датой",
    "The input does not fit the date format '%format%'" => "Значение не соответствует формату даты '%format%'",

    // Zend\Validator\DateStep
    "Invalid type given. String, integer, array or DateTime expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом, массивом или объектом DateTime",
    "The input does not appear to be a valid date" => "Значение не является корректной датой",
    "The input does not fit the date format '%format%'" => "Значение не соответствует формату даты '%format%'",
    "The input is not a valid step" => "Значение не является корректным шагом",

    // Zend\Validator\Db\AbstractDb
    "No record matching the input was found" => "Совпадающих со значением записей не найдено",
    "A record matching the input was found" => "Найдена совпадающая со значением запись",

    // Zend\Validator\Digits
    "The input must contain only digits" => "Значение должно содержать только цифры",
    "The input is an empty string" => "Значение не может быть пустой строкой",
    "Invalid type given. String, integer or float expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом или числом с плавающей точкой",

    // Zend\Validator\EmailAddress
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input is not a valid email address. Use the basic format local-part@hostname" => "Недопустимый адрес электронной почты. Введите его в формате имя@домен",
    "'%hostname%' is not a valid hostname for the email address" => "'%hostname%' недопустимое имя хоста в адресе электронной почты",
    "'%hostname%' does not appear to have any valid MX or A records for the email address" => "'%hostname%' не имеет корректной MX- или A-записи об адресе электронной почты",
    "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network" => "'%hostname%' не является маршрутизируемым сегментом сети. Адрес электронной почты не может быть получен из публичной сети",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart% не соответствует формату dot-atom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' не соответствует формату quoted-string",
    "'%localPart%' is not a valid local part for the email address" => "'%localPart%' недопустимое имя для адреса электронной почты",
    "The input exceeds the allowed length" => "Адрес электронной почты превышает допустимую длину",

    // Zend\Validator\Explode
    "Invalid type given" => "Неверный тип данных",

    // Zend\Validator\File\Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Слишком много файлов, максимально разрешено - '%max%', а получено - '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Слишком мало файлов, минимально разрешено - '%min%', а получено - '%count%'",

    // Zend\Validator\File\Crc32
    "File does not match the given crc32 hashes" => "Файл не соответствует заданному crc32 хешу",
    "A crc32 hash could not be evaluated for the given file" => "crc32 хеш не может быть вычислен для данного файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\ExcludeExtension
    "File has an incorrect extension" => "Файл имеет недопустимое расширение",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\Exists
    "File does not exist" => "Файл не существует",

    // Zend\Validator\File\Extension
    "File has an incorrect extension" => "Неверное расширение файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Общий размер файлов не должен превышать '%max%', сейчас - '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Общий размер файлов не должен быть менее '%min%', сейчас - '%size%'",
    "One or more files can not be read" => "Один или более файлов не могут быть прочитаны",

    // Zend\Validator\File\Hash
    "File does not match the given hashes" => "Файл не соответствует указанному хешу",
    "A hash could not be evaluated for the given file" => "Хеш не может быть подсчитан для указанного файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\ImageSize
    "Maximum allowed width for image should be '%maxwidth%' but '%width%' detected" => "Максимально разрешённая ширина изображения должна быть '%maxwidth%', сейчас - '%width%'",
    "Minimum expected width for image should be '%minwidth%' but '%width%' detected" => "Минимально ожидаемая ширина изображения должна быть '%minwidth%', сейчас - '%width%'",
    "Maximum allowed height for image should be '%maxheight%' but '%height%' detected" => "Максимально разрешённая высота изображения должна быть '%maxheight%', сейчас - '%height%'",
    "Minimum expected height for image should be '%minheight%' but '%height%' detected" => "Минимально ожидаемая высота изображения должна быть '%minheight%', сейчас - '%height%'",
    "The size of image could not be detected" => "Невозможно определить размер изображения",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\IsCompressed
    "File is not compressed, '%type%' detected" => "Файл не является сжатым. MIME-тип файла - '%type%'",
    "The mimetype could not be detected from the file" => "Не удается определить MIME-тип файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\IsImage
    "File is no image, '%type%' detected" => "Файл не является изображением. MIME-тип файла - '%type%'",
    "The mimetype could not be detected from the file" => "Не удается определить MIME-тип файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\Md5
    "File does not match the given md5 hashes" => "Файл не соответствует указанному md5 хешу",
    "An md5 hash could not be evaluated for the given file" => "md5 хеш не может быть вычислен для указанного файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\MimeType
    "File has an incorrect mimetype of '%type%'" => "MIME-тип '%type%' файла недопустим",
    "The mimetype could not be detected from the file" => "Не удается определить MIME-тип файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\NotExists
    "File exists" => "Файл уже существует",

    // Zend\Validator\File\Sha1
    "File does not match the given sha1 hashes" => "Файл не соответствует указаному хешу sha1",
    "A sha1 hash could not be evaluated for the given file" => "Хеш sha1 не может быть подсчитан для указанного файла",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\Size
    "Maximum allowed size for file is '%max%' but '%size%' detected" => "Максимальный разрешенный размер файла '%max%', сейчас - '%size%'",
    "Minimum expected size for file is '%min%' but '%size%' detected" => "Минимальный ожидаемый размер файла '%min%', сейчас - '%size%'",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\File\Upload
    "File '%value%' exceeds the defined ini size" => "Размер файла '%value%' превышает допустимый размер, указанный в php.ini",
    "File '%value%' exceeds the defined form size" => "Размер файла '%value%' превышает допустимый размер, указанный в форме",
    "File '%value%' was only partially uploaded" => "Файл '%value%' был загружен только частично",
    "File '%value%' was not uploaded" => "Файл '%value%' не был загружен",
    "No temporary directory was found for file '%value%'" => "Не найдена временная директория для файла '%value%'",
    "File '%value%' can't be written" => "Файл '%value%' не может быть записан",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP расширение возвратило ошибку во время загрузки файла '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Файл '%value%' загружен некорректно. Возможна это атака",
    "File '%value%' was not found" => "Файл '%value%' не найден",
    "Unknown error while uploading file '%value%'" => "Во время загрузки файла '%value%' произошла неизвестная ошибка",

    // Zend\Validator\File\UploadFile
    "File exceeds the defined ini size" => "Размер файла превышает допустимый размер, указанный в php.ini",
    "File exceeds the defined form size" => "Размер файла превышает допустимый размер, указанный в форме",
    "File was only partially uploaded" => "Файл был загружен только частично",
    "File was not uploaded" => "Файл не был загружен",
    "No temporary directory was found for file" => "Не найдена временная директория для файла",
    "File can't be written" => "Файл не может быть записан",
    "A PHP extension returned an error while uploading the file" => "PHP расширение возвратило ошибку во время загрузки файла",
    "File was illegally uploaded. This could be a possible attack" => "Файл загружен некорректно. Возможна это атака",
    "File was not found" => "Файл не найден",
    "Unknown error while uploading file" => "Во время загрузки файла произошла неизвестная ошибка",

    // Zend\Validator\File\WordCount
    "Too many words, maximum '%max%' are allowed but '%count%' were counted" => "Слишком много слов, разрешено максимум '%max%' слов, а сейчас - '%count%'",
    "Too few words, minimum '%min%' are expected but '%count%' were counted" => "Слишком мало слов, разрешено минимум '%min%' слов, а сейчас - '%count%'",
    "File is not readable or does not exist" => "Файл не может быть прочитан или не существует",

    // Zend\Validator\GreaterThan
    "The input is not greater than '%min%'" => "Значение не превышает '%min%'",
    "The input is not greater or equal than '%min%'" => "Значение не превышает или не равно '%min%'",

    // Zend\Validator\Hex
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input contains non-hexadecimal characters" => "Значение должно содержать только шестнадцатиричные символы",

    // Zend\Validator\Hostname
    "The input appears to be a DNS hostname but the given punycode notation cannot be decoded" => "Значение похоже на DNS имя хоста, но указанное значение не может быть преобразованно в допустимый для DNS набор символов",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input appears to be a DNS hostname but contains a dash in an invalid position" => "Значение похоже на DNS имя хоста, но знак '-' находится в недопустимом месте",
    "The input does not match the expected structure for a DNS hostname" => 'Значение не соответствует структуре DNS имени хоста',
    "The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "Значение похоже на DNS имя хоста, но оно не соответствует шаблону для доменных имен верхнего уровня '%tld%'",
    "The input does not appear to be a valid local network name" => "Значение является недопустимым локальным сетевым адресом",
    "The input does not appear to be a valid URI hostname" => "Значение является недопустимым URI имени хоста",
    "The input appears to be an IP address, but IP addresses are not allowed" => "Значение похоже на IP-адрес, но IP-адреса не разрешены",
    "The input appears to be a local network name but local network names are not allowed" => 'Значение похоже на адрес в локальной сети, но локальные адреса не разрешены',
    "The input appears to be a DNS hostname but cannot extract TLD part" => "Значение похоже на DNS имя хоста, но не удаётся извлечь домен верхнего уровня",
    "The input appears to be a DNS hostname but cannot match TLD against known list" => "Значение похоже на DNS имя хоста, но оно не дожно быть из списка доменов верхнего уровня",

    // Zend\Validator\Iban
    "Unknown country within the IBAN" => "Не известная страна IBAN",
    "Countries outside the Single Euro Payments Area (SEPA) are not supported" => "Страны не входящие в Единую зону платежей в евро (SEPA) не поддерживаются",
    "The input has a false IBAN format" => "Неверный IBAN формат",
    "The input has failed the IBAN check" => "Значение не прошло проверку IBAN",

    // Zend\Validator\Identical
    "The two given tokens do not match" => "Значения не совпадают",
    "No token was provided to match against" => "Не было указано значение для проверки на идентичность",

    // Zend\Validator\InArray
    "The input was not found in the haystack" => "Значение не найдено в имеющихся допустимых значениях",

    // Zend\Validator\Ip
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input does not appear to be a valid IP address" => "Значение не является корректным IP-адресом",

    // Zend\Validator\IsInstanceOf
    "The input is not an instance of '%className%'" => "Значение не является экземпляром объекта '%className%'",

    // Zend\Validator\Isbn
    "Invalid type given. String or integer expected" => "Недопустимый тип данных. Значение должно быть строкой или целым числом",
    "The input is not a valid ISBN number" => "Значение не является корректным номером ISBN",

    // Zend\Validator\LessThan
    "The input is not less than '%max%'" => "Значение не меньше '%max%'",
    "The input is not less or equal than '%max%'" => "Значение не меньше или не равно '%max%'",

    // Zend\Validator\NotEmpty
    "Value is required and can't be empty" => "Значение обязательно для заполнения и не может быть пустым",
    "Invalid type given. String, integer, float, boolean or array expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом, числом с плавающей точкой, булевым значением или массивом",

    // Zend\Validator\Regex
    "Invalid type given. String, integer or float expected" => "Недопустимый тип данных. Значение должно быть строкой, целым числом или числом с плавающей точкой",
    "The input does not match against pattern '%pattern%'" => "Значение не соответствует шаблону '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Возникла внутренняя ошибка во время использования шаблона '%pattern%'",

    // Zend\Validator\Sitemap\Changefreq
    "The input is not a valid sitemap changefreq" => "Недопустимое значение для sitemap changefreq",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",

    // Zend\Validator\Sitemap\Lastmod
    "The input is not a valid sitemap lastmod" => "Недопустимое значение для sitemap lastmod",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",

    // Zend\Validator\Sitemap\Loc
    "The input is not a valid sitemap location" => "Недопустимое значение для sitemap location",
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",

    // Zend\Validator\Sitemap\Priority
    "The input is not a valid sitemap priority" => "Недопустимое значение для sitemap priority",
    "Invalid type given. Numeric string, integer or float expected" => "Недопустимый тип данных. Значение должно быть цифровым, целым числом или числом с плавающей точкой",

    // Zend\Validator\Step
    "Invalid value given. Scalar expected" => "Неверное значение. Значение должно быть скалярным",
    "The input is not a valid step" => "Неверный шаг",

    // Zend\Validator\StringLength
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input is less than %min% characters long" => "Значение меньше разрешенной минимальной длины в %min% символов",
    "The input is more than %max% characters long" => "Значение больше разрешенной максимальной длины в %max% символов",

    // Zend\Validator\Timezone
    "Invalid timezone given." => "Неверный часовой пояс.",
    "Invalid timezone location given." => "Неверное расположение часового пояса.",
    "Invalid timezone abbreviation given." => "Неверная аббревиатура часового пояса.",

    // Zend\Validator\Uri
    "Invalid type given. String expected" => "Недопустимый тип данных. Значение должно быть строкой",
    "The input does not appear to be a valid Uri" => "Значение не похоже на корректный Uri",
];
