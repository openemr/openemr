# File Validation Classes

Zend Framework comes with a set of classes for validating both files and
uploaded files, such as file size validation and CRC checking.

- [Count](count.md)
- [crc32](crc32.md)
- [ExcludeExtension](exclude-extension.md)
- [ExcludeMimeType](exclude-mime-type.md)
- [Exists](exists.md)
- [Extension](extension.md)
- [FilesSize](files-size.md)
- [Hash](hash.md)
- [ImageSize](image-size.md)
- [IsCompressed](is-compressed.md)
- [IsImage](is-image.md)
- [Md5](md5.md)
- [MimeType](mime-type.md)
- [NotExists](not-exists.md)
- [Sha1](sha1.md)
- [Size](size.md)
- [Upload](upload.md)
- [UploadFile](upload-file.md)
- [WordCount](word-count.md)

> ### Validation argument
>
> All of the File validators' `isValid()` methods support both a file path
> `string` *or* a `$_FILES` array as the supplied argument. When a `$_FILES`
> array is passed in, the `tmp_name` is used for the file path.
