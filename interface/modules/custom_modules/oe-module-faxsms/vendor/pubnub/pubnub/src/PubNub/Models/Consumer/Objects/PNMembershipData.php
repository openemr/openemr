/** @flow */

import type { ChannelMetadata } from '../channel/channel';

export type Membership = {|
  channel: ChannelMetadata,
  custom: ?any,
  updated: string,
  eTag: string,
|};

export type PaginatedResultParams = {
  filter?: string,
  sort?: { [key: string]: 'asc' | 'desc' | null },
  limit?: number,
  page?: {|
    next?: string,
    prev?: string,
  |},
  include?: {|
    totalCount?: boolean,
    customFields?: boolean, // custom
    channelFields?: boolean, // channel
    customChannelFields?: boolean, // channel.custom
  |},
};
