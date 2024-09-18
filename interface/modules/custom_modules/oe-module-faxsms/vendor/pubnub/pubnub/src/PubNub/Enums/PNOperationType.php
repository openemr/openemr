<?php

namespace PubNub\Enums;

class PNOperationType
{
    const PNSubscribeOperation = 1;
    const PNUnsubscribeOperation = 2;

    const PNPublishOperation = 3;

    const PNHistoryOperation = 4;
    const PNFetchMessagesOperation = 5;

    const PNWhereNowOperation = 6;

    const PNHeartbeatOperation = 7;
    const PNSetStateOperation = 8;
    const PNAddChannelsToGroupOperation = 9;
    const PNRemoveChannelsFromGroupOperation = 10;
    const PNChannelGroupsOperation = 11;
    const PNRemoveGroupOperation = 12;
    const PNChannelsForGroupOperation = 13;
    const PNPushNotificationEnabledChannelsOperation = 14;
    const PNAddPushNotificationsOnChannelsOperation = 15;
    const PNRemovePushNotificationsFromChannelsOperation = 16;
    const PNRemoveAllPushNotificationsOperation = 17;
    const PNTimeOperation = 18;

    // CREATED
    const PNHereNowOperation = 19;
    const PNGetState = 20;
    const PNAccessManagerAudit = 21;
    const PNAccessManagerGrant = 22;
    const PNAccessManagerRevoke = 23;
    const PNHistoryDeleteOperation = 24;
    const PNMessageCountOperation = 25;

      // Objects API v2
    //   UUID
    const PNGetAllUUIDMetadataOperation = 26;
    const PNGetUUIDMetadataOperation = 27;
    const PNSetUUIDMetadataOperation = 28;
    const PNRemoveUUIDMetadataOperation = 29;
    //   channel
    const PNGetAllChannelMetadataOperation = 30;
    const PNGetChannelMetadataOperation = 31;
    const PNSetChannelMetadataOperation = 32;
    const PNRemoveChannelMetadataOperation = 33;
    //   member
    const PNGetMembersOperation = 34;
    const PNSetMembersOperation = 35;
    const PNRemoveMembersOperation = 36;
    //   membership
    const PNGetMembershipsOperation = 37;
    const PNSetMembershipsOperation = 38;
    const PNRemoveMembershipsOperation = 39;

    const PNSignalOperation = 40;

    // AccessManager v3
    const PNAccessManagerGrantToken = 41;
    const PNAccessManagerRevokeToken = 42;

    const PNGetFilesAction = 46;
    const PNDeleteFileOperation = 47;
    const PNGetFileDownloadURLAction = 48;
    const PNFetchFileUploadS3DataAction = 49;
    const PNDownloadFileAction = 50;
    const PNSendFileAction = 51;
    const PNSendFileNotification = 52;
}
