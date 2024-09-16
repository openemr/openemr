/** @flow */

import type { UUIDMetadata } from '../uuid/uuid';

export type Member = {|
  uuid: UUIDMetadata,
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
    UUIDFields?: boolean, // uuid
    customUUIDFields?: boolean, // uuid.custom
  |},
};
