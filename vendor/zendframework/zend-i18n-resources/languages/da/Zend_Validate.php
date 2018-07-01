<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * DA-Revision: 15.Oct.2015
 */

return [
    // Zend\Authentication\Validator\Authentication
    "Invalid identity" => "Ugyldig bruger",
    "Identity is ambiguous" => "Denne bruger findes allerede",
    "Invalid password" => "Ugyldigt kodeord",
    "Authentication failed" => "Log ind mislykkedes",

    // Zend\I18n\Validator\Alnum
    "Invalid type given. String, integer or float expected" => "Ugyldig indtastning. Indtast streng, heltal eller kommatal",
    "The input contains characters which are non alphabetic and no digits" => "Indtastningen indeholder tegn, som er ikke-alfabetiske eller ikke-numeriske",
    "The input is an empty string" => "Indtastningen er en tom streng",

    // Zend\I18n\Validator\Alpha
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input contains non alphabetic characters" => "Indtastningen indeholder ikke-alfabetiske tegn ",
    "The input is an empty string" => " Indtastningen er en tom streng",

    // Zend\I18n\Validator\DateTime
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input does not appear to be a valid datetime" => "Den indtastede dato/tid er ikke gyldig",

    // Zend\I18n\Validator\IsFloat
    "Invalid type given. String, integer or float expected" => "Ugyldig indtastning. Indtast streng, heltal eller kommatal",
    "The input does not appear to be a float" => "Indtastningen indeholder ikke et kommatal",

    // Zend\I18n\Validator\IsInt
    "Invalid type given. String or integer expected" => "Ugyldig indtastning. Indtast streng eller heltal",
    "The input does not appear to be an integer" => "Indtastningen indeholder ikke et heltal",

    // Zend\I18n\Validator\PhoneNumber
    "The input does not match a phone number format" => "Indtastningen er ikke et telefonnummerformat",
    "The country provided is currently unsupported" => "Det valgte land understøttes ikke",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",

    // Zend\I18n\Validator\PostCode
    "Invalid type given. String or integer expected" => "Ugyldig indtastning. Indtast streng eller heltal",
    "The input does not appear to be a postal code" => "Indtastningen er ikke et postnummer",
    "An exception has been raised while validating the input" => "Der opstod en fejl ved valideringen af indtastningen ",

    // Zend\Validator\Barcode
    "The input failed checksum validation" => "Indtastningen stemmer ikke overens med valideringen af kontrolsummen",
    "The input contains invalid characters" => "Indtastningen indeholder ugyldige tegn",
    "The input should have a length of %length% characters" => "Indtastningen skal indeholde %length% tegn",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",

    // Zend\Validator\Between
    "The input is not between '%min%' and '%max%', inclusively" => "Indtastningen er ikke mellem '%min%' og '%max%' tegn",
    "The input is not strictly between '%min%' and '%max%'" => "Indtastningen er ikke mellem '%min%' og '%max%' tegn",

    // Zend\Validator\Bitwise
    "The input has no common bit set with '%control%'" => "Indtastningen har ingen fælles bit med '%control%'",
    "The input doesn't have the same bits set as '%control%'" => "Indtastningen har ikke de samme bits som '%control%'",
    "The input has common bit set with '%control%'" => "Indtastningen har fælles bit med '%control%'",

    // Zend\Validator\Callback
    "The input is not valid" => "Ugyldig indtastning",
    "An exception has been raised within the callback" => "Der opstod en fejl i forbindelse med callback'et",

    // Zend\Validator\CreditCard
    "The input seems to contain an invalid checksum" => "Den indtastede kontrolsum er ugyldig",
    "The input must contain only digits" => " Indtastningen må kun indeholde tal",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input contains an invalid amount of digits" => "Indtastningen indeholder ugyldige tal",
    "The input is not from an allowed institute" => "Indtastningen stammer ikke fra et godkendt kreditinstitut",
    "The input seems to be an invalid credit card number" => "Det indtastede kreditkortnummer er ugyldigt",
    "An exception has been raised while validating the input" => "Der opstod en fejl i forbindelse med valideringen af indtastningen",

    // Zend\Validator\Csrf
    "The form submitted did not originate from the expected site" => "Den afsendte formular stammer ikke fra den forventede side ",

    // Zend\Validator\Date
    "Invalid type given. String, integer, array or DateTime expected" => "Ugyldig indtastning. Indtast streng, heltal, array eller DateTime, ",
    "The input does not appear to be a valid date" => "Den indtastede dato er ugyldig",
    "The input does not fit the date format '%format%'" => "Indtastningen stemmer ikke overens med datoformatet '%format%'",

    // Zend\Validator\DateStep
    "Invalid type given. String, integer, array or DateTime expected" => "Ugyldig indtastning. Indtast streng, heltal, array eller DateTime ",
    "The input does not appear to be a valid date" => "Den indtastede dato er ugyldig",
    "The input does not fit the date format '%format%'" => "Indtastningen stemmer ikke overens med datoformatet '%format%'",
    "The input is not a valid step" => "Denne indtastning er ikke gyldig",

    // Zend\Validator\Db\AbstractDb
    "No record matching the input was found" => "Ingen forekomster fundet",
    "A record matching the input was found" => "Forekomster fundet",

    // Zend\Validator\Digits
    "The input must contain only digits" => "Indtastningen må kun indeholde tal",
    "The input is an empty string" => "Indtastningen er en tom streng",
    "Invalid type given. String, integer or float expected" => "Ugyldig indtastning. Indtast streng, heltal eller kommatal",

    // Zend\Validator\EmailAddress
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input is not a valid email address. Use the basic format local-part@hostname" => "Den indtastede e-mail-adresse er ugyldig. Brug formatet local-part@hostname",
    "'%hostname%' is not a valid hostname for the email address" => "'%hostname%' er ikke et gyldigt domæne for e-mail-adressen",
    "'%hostname%' does not appear to have any valid MX or A records for the email address" => "'%hostname%' har ikke en gyldig MX eller A-record for e-mail-adressen",
    "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network" => "'%hostname%' er ikke en del af et kompatibelt netværk. E-mail-adressen må ikke fjernes fra det offentlige netværk",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' stemmer ikke overens med dot-atom-formatet",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' stemmer ikke overens med strengformatet",
    "'%localPart%' is not a valid local part for the email address" => "'%localPart%' er ikke en gyldig lokal del af e-mail-adressen",
    "The input exceeds the allowed length" => "Indtastningen overskrider den tilladte længde",

    // Zend\Validator\Explode
    "Invalid type given" => "Ugyldig indtastning",

    // Zend\Validator\File\Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Indeholder for mange filer, max. '%max%' er tilladt men '%count%' er angivet",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Indeholder for få filer, forventet antal filer er '%min%' men '%count%' er angivet",

    // Zend\Validator\File\Crc32
    "File does not match the given crc32 hashes" => "Filen stemmer ikke overens med de angivne crc32 hashes",
    "A crc32 hash could not be evaluated for the given file" => "Et crc32-hash kunne ikke genkendes for den angivne fil",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\ExcludeExtension
    "File has an incorrect extension" => "Forkert filtype",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\Exists
    "File does not exist" => "Filen eksisterer ikke",

    // Zend\Validator\File\Extension
    "File has an incorrect extension" => "Forkert filtype",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Filerne må samlet max. fylde '%max%', men disse filer fylder '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Filerne skal min. fylde '%min%', men disse filer fylder '%size%'",
    "One or more files can not be read" => "En eller flere filer kan ikke læses",

    // Zend\Validator\File\Hash
    "File does not match the given hashes" => "Filen stemmer ikke overens med de angivne tegn",
    "A hash could not be evaluated for the given file" => "Et tegn kunne ikke genkendes for den valgte fil",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\ImageSize
    "Maximum allowed width for image should be '%maxwidth%' but '%width%' detected" => "Den max. tilladte bredde for et billede er '%maxwidth%', men dette billede er '%width%'",
    "Minimum expected width for image should be '%minwidth%' but '%width%' detected" => "Den mindste bredde for billeder skal være '%minwidth%', men dette billede er '%width%'",
    "Maximum allowed height for image should be '%maxheight%' but '%height%' detected" => "Den max. tilladte højde for billeder er '%maxheight%', men dette billede er '%height%'",
    "Minimum expected height for image should be '%minheight%' but '%height%' detected" => "Den mindste højde for billeder er '%minheight%', men dette billede er '%height%'",
    "The size of image could not be detected" => "Størrelsen på billedet kunne ikke findes",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",
    // Zend\Validator\File\IsCompressed

    "File is not compressed, '%type%' detected" => "Filen er ikke komprimeret, '%type%' fundet",
    "The mimetype could not be detected from the file" => "Mime-typen kunne ikke læses fra filen",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\IsImage
    "File is no image, '%type%' detected" => "Filen er ikke et billede, '%type%' fundet",
    "The mimetype could not be detected from the file" => "Mime-typen kunne ikke læses fra filen",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\Md5
    "File does not match the given md5 hashes" => "Filen er ikke i overensstemmelse med de angivne md5-hash",
    "An md5 hash could not be evaluated for the given file" => "Et md5-hash kunne ikke genkendes for den valgte fil",
    "File is not readable or does not exist" => " Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\MimeType
    "File has an incorrect mimetype of '%type%'" => "Filens mime-type '%type%' er ikke korrekt",
    "The mimetype could not be detected from the file" => "Mime-typen kunne ikke læses fra filen",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\NotExists
    "File exists" => "Filen eksisterer",

    // Zend\Validator\File\Sha1
    "File does not match the given sha1 hashes" => "Filen er ikke i overensstemmelse med de angivne sha1-hashes",
    "A sha1 hash could not be evaluated for the given file" => "Et sha1-hash kunne ikke genkendes for den valgte fil",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\Size
    "Maximum allowed size for file is '%max%' but '%size%' detected" => "Den max. tilladte størrelse for filer er '%max%', men filen fylder '%size%'",
    "Minimum expected size for file is '%min%' but '%size%' detected" => "Den mindste størrelse for filer bør være '%min%', men filen fylder '%size%'",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\File\Upload
    "File '%value%' exceeds the defined ini size" => "Filen '%value%' overskrider den definerede ini-størrelse",
    "File '%value%' exceeds the defined form size" => "Filen '%value%' overskrider den definerede formularstørrelse",
    "File '%value%' was only partially uploaded" => "Filen '%value%' blev kun delvist uploadet",
    "File '%value%' was not uploaded" => "Filen '%value%' blev ikke uploadet",
    "No temporary directory was found for file '%value%'" => "Ingen midlertidig mappe blev fundet for filen '%value%'",
    "File '%value%' can't be written" => "Filen '%value%' kan ikke skrives",
    "A PHP extension returned an error while uploading the file '%value%'" => "En PHP-udvidelse forårsagede en fejl ved upload af filen '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Filen '%value%' blev ulovligt uploadet. Dette kan være et muligt angreb",
    "File '%value%' was not found" => "Filen '%value%' blev ikke fundet",
    "Unknown error while uploading file '%value%'" => "Ukendt fejl opstod ved upload af filen '%value%'",

    // Zend\Validator\File\UploadFile
    "File exceeds the defined ini size" => "Filen overskrider den definerede ini-størrelse",
    "File exceeds the defined form size" => "Filen overskrider den definerede formularstørrelse",
    "File was only partially uploaded" => "Filen blev kun delvist uploadet",
    "File was not uploaded" => "Filen blev ikke uploadet",
    "No temporary directory was found for file" => "Ingen midlertidig mappe blev fundet for filen",
    "File can't be written" => "Filen kan ikke skrives",
    "A PHP extension returned an error while uploading the file" => "En PHP-udvidelse forårsagede en fejlved upload af filen",
    "File was illegally uploaded. This could be a possible attack" => "Filen blev ulovligt uploadet. Dette kan være et muligt angreb",
    "File was not found" => "Filen blev ikke fundet",
    "Unknown error while uploading file" => "Der opstod en ukendt fejl under upload af filen",

    // Zend\Validator\File\WordCount
    "Too many words, maximum '%max%' are allowed but '%count%' were counted" => "Indeholder for mange ord, max. '%max%' ord er tilladt, men '%count%' ord er optalt",
    "Too few words, minimum '%min%' are expected but '%count%' were counted" => "Indeholder for få ord, bør indeholde min. '%min%' ord, men '%count%' ord er optalt",
    "File is not readable or does not exist" => "Filen kan ikke læses eller eksisterer ikke",

    // Zend\Validator\GreaterThan
    "The input is not greater than '%min%'" => "Indtastningen er ikke større end '%min%'",
    "The input is not greater or equal than '%min%'" => "Indtastningen indeholder ikke mere end, eller er præcis, '%min%'",

    // Zend\Validator\Hex
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input contains non-hexadecimal characters" => "Indtastningen indeholder ikke-heksadecimale tegn",

    // Zend\Validator\Hostname
    "The input appears to be a DNS hostname but the given punycode notation cannot be decoded" => "Indtastningen ligner et DNS hostnavn, men den angivne punycode-notation kan ikke afkodes",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input appears to be a DNS hostname but contains a dash in an invalid position" => "Indtastningen ligner et DNS hostnavn men indeholder en ugyldig bindestreg",
    "The input does not match the expected structure for a DNS hostname" => " Indtastningen er ikke i overensstemmelse med strukturen for et DNS hostnavn",
    "The input appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "Indtastningen ligner et DNS hostnavn men navnestrukturen er ikke i overensstemmelse med strukturen for TLD '%tld%'",
    "The input does not appear to be a valid local network name" => "Indtastningen ligner ikke et gyldigt navn på et lokalt netværk",
    "The input does not appear to be a valid URI hostname" => "Indtastningen ligner ikke et gyldigt URI-hostnavn",
    "The input appears to be an IP address, but IP addresses are not allowed" => "Indtastningen ligner en IP-adresse, men IP-adresser er ikke tilladt",

    "The input appears to be a local network name but local network names are not allowed" => "Indtastningen ligner navnet på et lokalt netværk, hvilket ikke er tilladt",
    "The input appears to be a DNS hostname but cannot extract TLD part" => "Indtastningen ligner et DNS hostnavn, men der mangler et TLD",
    "The input appears to be a DNS hostname but cannot match TLD against known list" => "Indtastningen ligner et DNS hostnavn, men TLD genkendes ikke",

    // Zend\Validator\Iban
    "Unknown country within the IBAN" => "Ukendt land inden for IBAN-nummeret",
    "Countries outside the Single Euro Payments Area (SEPA) are not supported" => "Lande uden for Single Euro Payments Area (SEPA) understøttes ikke",
    "The input has a false IBAN format" => "Indtastningen har et falsk IBAN-format",
    "The input has failed the IBAN check" => "Indtastningen blev ikke godkendt i IBAN-kontrollen",

    // Zend\Validator\Identical
    "The two given tokens do not match" => "De to angivne værdier stemmer ikke overens",
    "No token was provided to match against" => "Der er ikke indtastet nogen værdi til sammenligning",

    // Zend\Validator\InArray
    "The input was not found in the haystack" => "Indtastningen blev ikke fundet",

    // Zend\Validator\Ip
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input does not appear to be a valid IP address" => "Den indtastede IP-adresse er ugyldig",

    // Zend\Validator\IsInstanceOf
    "The input is not an instance of '%className%'" => "Indtastningen er ikke af typen '%className%'",

    // Zend\Validator\Isbn
    "Invalid type given. String or integer expected" => "Ugyldig indtastning. Indtast streng eller heltal",
    "The input is not a valid ISBN number" => "Det indtastede ISBN-nummer er ugyldigt",

    // Zend\Validator\LessThan
    "The input is not less than '%max%'" => "Indtastningen er ikke mindre end '%max%'",
    "The input is not less or equal than '%max%'" => "Indtastningen er ikke mindre end, eller er det samme som, '%max%'",

    // Zend\Validator\NotEmpty
    "Value is required and can't be empty" => "Værdi skal udfyldes",
    "Invalid type given. String, integer, float, boolean or array expected" => "Ugyldig indtastning. Indtaststreng, heltal, kommatal, boolesk værdi eller array",

    // Zend\Validator\Regex
    "Invalid type given. String, integer or float expected" => "Ugyldig indtastning. Indtast streng, heltal eller kommatal",
    "The input does not match against pattern '%pattern%'" => " Indtastningen stemmer ikke overens med strukturen '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Der opstod en intern fejl ved brug af strukturen '%pattern%'",

    // Zend\Validator\Sitemap\Changefreq
    "The input is not a valid sitemap changefreq" => "Indtastningen er ikke en gyldig sitemap changefreq",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",

    // Zend\Validator\Sitemap\Lastmod
    "The input is not a valid sitemap lastmod" => "Indtastningen er ikke en gyldig sitemap lastmod",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",

    // Zend\Validator\Sitemap\Loc
    "The input is not a valid sitemap location" => "Indtastningen er ikke en gyldig sitemap location",
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",

    // Zend\Validator\Sitemap\Priority
    "The input is not a valid sitemap priority" => "Indtastningen er ikke en gyldig sitemap-prioritet",
    "Invalid type given. Numeric string, integer or float expected" => "Ugyldig indtastning. Indtast numerisk streng, heltal eller kommatal",

    // Zend\Validator\Step
    "Invalid value given. Scalar expected" => "Ugyldig værdi. Indtast skalar værdi",
    "The input is not a valid step" => "Denne indtastning er ikke gyldig",

    // Zend\Validator\StringLength
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input is less than %min% characters long" => "Indtastningen indeholder færre end %min% tegn",
    "The input is more than %max% characters long" => "Indtastningen indeholder flere end %max% tegn",

    // Zend\Validator\Timezone
    "Invalid timezone given." => "Ugyldig tidszone.",
    "Invalid timezone location given." => "Ugyldig tidszone/sted.",
    "Invalid timezone abbreviation given." => "Ugyldig tidszone forkortelse.",

    // Zend\Validator\Uri
    "Invalid type given. String expected" => "Ugyldig indtastning. Indtast streng",
    "The input does not appear to be a valid Uri" => "Indtastningen ligner ikke en gyldig Uri",
];
