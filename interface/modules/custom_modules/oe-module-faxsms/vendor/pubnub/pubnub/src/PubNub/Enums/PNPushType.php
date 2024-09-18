<?php

namespace PubNub\Enums;

class PNPushType
{
    public const APNS = "apns";
    public const APNS2 = "apns2";
    public const MPNS = "mpns";
    public const GCM = "gcm";
    public const FCM = "fcm";

    public static function all()
    {
        return [
            self::APNS,
            self::APNS2,
            self::MPNS,
            self::GCM,
            self::FCM
        ];
    }
}
