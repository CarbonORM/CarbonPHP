
export interface stringMap {
    [key: string]: string;
}

export interface stringNumberMap {
    [key: string]: string | number;
}

export interface RegExpMap {
    [key: string]: RegExp | RegExpMap;
}

export interface complexMap {
    [key: string]: stringMap | stringNumberMap | stringMap[] | RegExpMap;
}
 
export interface iTypeValidation {
    MYSQL_TYPE: string,
    MAX_LENGTH: string,
    AUTO_INCREMENT: boolean,
    SKIP_COLUMN_IN_POST: boolean
}

export type RestTableNames = 'carbon_carbons'
	| 'carbon_comments'
	| 'carbon_documentation'
	| 'carbon_feature_group_references'
	| 'carbon_features'
	| 'carbon_group_references'
	| 'carbon_groups'
	| 'carbon_history_logs'
	| 'carbon_location_references'
	| 'carbon_locations'
	| 'carbon_photos'
	| 'carbon_reports'
	| 'carbon_user_followers'
	| 'carbon_user_groups'
	| 'carbon_user_messages'
	| 'carbon_user_sessions'
	| 'carbon_user_tasks'
	| 'carbon_users';

export type RestShortTableNames = 'carbons'
	| 'comments'
	| 'documentation'
	| 'feature_group_references'
	| 'features'
	| 'group_references'
	| 'groups'
	| 'history_logs'
	| 'location_references'
	| 'locations'
	| 'photos'
	| 'reports'
	| 'user_followers'
	| 'user_groups'
	| 'user_messages'
	| 'user_sessions'
	| 'user_tasks'
	| 'users';

export interface C6RestfulModel {
    TABLE_NAME: RestShortTableNames,
    PRIMARY: string[],
    COLUMNS: stringMap,
    REGEX_VALIDATION: RegExpMap,
    TYPE_VALIDATION: {[key: string]: iTypeValidation},
}



export interface  iCarbons {
      'entity_pk'?: string;
      'entity_fk'?: string;
      'entity_tag'?: string;
}

interface iDefineCarbons {
      'ENTITY_PK': string;
      'ENTITY_FK': string;
      'ENTITY_TAG': string;
}

export const carbons : C6RestfulModel & iDefineCarbons = {
    TABLE_NAME:'carbons',
    ENTITY_PK: 'carbon_carbons.entity_pk',
    ENTITY_FK: 'carbon_carbons.entity_fk',
    ENTITY_TAG: 'carbon_carbons.entity_tag',
    PRIMARY: [
        'carbon_carbons.entity_pk',
    ],
    COLUMNS: {
      'carbon_carbons.entity_pk':'entity_pk',
      'carbon_carbons.entity_fk':'entity_fk',
      'carbon_carbons.entity_tag':'entity_tag',
    },
    TYPE_VALIDATION: {
        'carbon_carbons.entity_pk': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_carbons.entity_fk': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_carbons.entity_tag': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iComments {
      'parent_id'?: string;
      'comment_id'?: string;
      'user_id'?: string;
      'comment'?: string;
}

interface iDefineComments {
      'PARENT_ID': string;
      'COMMENT_ID': string;
      'USER_ID': string;
      'COMMENT': string;
}

export const comments : C6RestfulModel & iDefineComments = {
    TABLE_NAME:'comments',
    PARENT_ID: 'carbon_comments.parent_id',
    COMMENT_ID: 'carbon_comments.comment_id',
    USER_ID: 'carbon_comments.user_id',
    COMMENT: 'carbon_comments.comment',
    PRIMARY: [
        'carbon_comments.comment_id',
    ],
    COLUMNS: {
      'carbon_comments.parent_id':'parent_id',
      'carbon_comments.comment_id':'comment_id',
      'carbon_comments.user_id':'user_id',
      'carbon_comments.comment':'comment',
    },
    TYPE_VALIDATION: {
        'carbon_comments.parent_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_comments.comment_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_comments.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_comments.comment': { 
            MYSQL_TYPE: 'blob', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iDocumentation {
      'documentation_uri'?: string;
      'documentation_data'?: string;
      'documentation_version'?: string;
      'documentation_active'?: number;
}

interface iDefineDocumentation {
      'DOCUMENTATION_URI': string;
      'DOCUMENTATION_DATA': string;
      'DOCUMENTATION_VERSION': string;
      'DOCUMENTATION_ACTIVE': string;
}

export const documentation : C6RestfulModel & iDefineDocumentation = {
    TABLE_NAME:'documentation',
    DOCUMENTATION_URI: 'carbon_documentation.documentation_uri',
    DOCUMENTATION_DATA: 'carbon_documentation.documentation_data',
    DOCUMENTATION_VERSION: 'carbon_documentation.documentation_version',
    DOCUMENTATION_ACTIVE: 'carbon_documentation.documentation_active',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_documentation.documentation_uri':'documentation_uri',
      'carbon_documentation.documentation_data':'documentation_data',
      'carbon_documentation.documentation_version':'documentation_version',
      'carbon_documentation.documentation_active':'documentation_active',
    },
    TYPE_VALIDATION: {
        'carbon_documentation.documentation_uri': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_documentation.documentation_data': { 
            MYSQL_TYPE: 'longblob', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_documentation.documentation_version': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '40', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_documentation.documentation_active': { 
            MYSQL_TYPE: 'tinyint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iFeature_Group_References {
      'feature_entity_id'?: string;
      'group_entity_id'?: string;
}

interface iDefineFeature_Group_References {
      'FEATURE_ENTITY_ID': string;
      'GROUP_ENTITY_ID': string;
}

export const feature_group_references : C6RestfulModel & iDefineFeature_Group_References = {
    TABLE_NAME:'feature_group_references',
    FEATURE_ENTITY_ID: 'carbon_feature_group_references.feature_entity_id',
    GROUP_ENTITY_ID: 'carbon_feature_group_references.group_entity_id',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_feature_group_references.feature_entity_id':'feature_entity_id',
      'carbon_feature_group_references.group_entity_id':'group_entity_id',
    },
    TYPE_VALIDATION: {
        'carbon_feature_group_references.feature_entity_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_feature_group_references.group_entity_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iFeatures {
      'feature_entity_id'?: string;
      'feature_code'?: string;
      'feature_creation_date'?: string;
}

interface iDefineFeatures {
      'FEATURE_ENTITY_ID': string;
      'FEATURE_CODE': string;
      'FEATURE_CREATION_DATE': string;
}

export const features : C6RestfulModel & iDefineFeatures = {
    TABLE_NAME:'features',
    FEATURE_ENTITY_ID: 'carbon_features.feature_entity_id',
    FEATURE_CODE: 'carbon_features.feature_code',
    FEATURE_CREATION_DATE: 'carbon_features.feature_creation_date',
    PRIMARY: [
        'carbon_features.feature_entity_id',
    ],
    COLUMNS: {
      'carbon_features.feature_entity_id':'feature_entity_id',
      'carbon_features.feature_code':'feature_code',
      'carbon_features.feature_creation_date':'feature_creation_date',
    },
    TYPE_VALIDATION: {
        'carbon_features.feature_entity_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_features.feature_code': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '30', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_features.feature_creation_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iGroup_References {
      'group_id'?: string;
      'allowed_to_grant_group_id'?: string;
}

interface iDefineGroup_References {
      'GROUP_ID': string;
      'ALLOWED_TO_GRANT_GROUP_ID': string;
}

export const group_references : C6RestfulModel & iDefineGroup_References = {
    TABLE_NAME:'group_references',
    GROUP_ID: 'carbon_group_references.group_id',
    ALLOWED_TO_GRANT_GROUP_ID: 'carbon_group_references.allowed_to_grant_group_id',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_group_references.group_id':'group_id',
      'carbon_group_references.allowed_to_grant_group_id':'allowed_to_grant_group_id',
    },
    TYPE_VALIDATION: {
        'carbon_group_references.group_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_group_references.allowed_to_grant_group_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iGroups {
      'group_name'?: string;
      'entity_id'?: string;
      'created_by'?: string;
      'creation_date'?: string;
}

interface iDefineGroups {
      'GROUP_NAME': string;
      'ENTITY_ID': string;
      'CREATED_BY': string;
      'CREATION_DATE': string;
}

export const groups : C6RestfulModel & iDefineGroups = {
    TABLE_NAME:'groups',
    GROUP_NAME: 'carbon_groups.group_name',
    ENTITY_ID: 'carbon_groups.entity_id',
    CREATED_BY: 'carbon_groups.created_by',
    CREATION_DATE: 'carbon_groups.creation_date',
    PRIMARY: [
        'carbon_groups.entity_id',
    ],
    COLUMNS: {
      'carbon_groups.group_name':'group_name',
      'carbon_groups.entity_id':'entity_id',
      'carbon_groups.created_by':'created_by',
      'carbon_groups.creation_date':'creation_date',
    },
    TYPE_VALIDATION: {
        'carbon_groups.group_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_groups.entity_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_groups.created_by': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_groups.creation_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iHistory_Logs {
      'history_uuid'?: string;
      'history_table'?: string;
      'history_type'?: string;
      'history_data'?: string;
      'history_original_query'?: string;
      'history_time'?: string;
}

interface iDefineHistory_Logs {
      'HISTORY_UUID': string;
      'HISTORY_TABLE': string;
      'HISTORY_TYPE': string;
      'HISTORY_DATA': string;
      'HISTORY_ORIGINAL_QUERY': string;
      'HISTORY_TIME': string;
}

export const history_logs : C6RestfulModel & iDefineHistory_Logs = {
    TABLE_NAME:'history_logs',
    HISTORY_UUID: 'carbon_history_logs.history_uuid',
    HISTORY_TABLE: 'carbon_history_logs.history_table',
    HISTORY_TYPE: 'carbon_history_logs.history_type',
    HISTORY_DATA: 'carbon_history_logs.history_data',
    HISTORY_ORIGINAL_QUERY: 'carbon_history_logs.history_original_query',
    HISTORY_TIME: 'carbon_history_logs.history_time',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_history_logs.history_uuid':'history_uuid',
      'carbon_history_logs.history_table':'history_table',
      'carbon_history_logs.history_type':'history_type',
      'carbon_history_logs.history_data':'history_data',
      'carbon_history_logs.history_original_query':'history_original_query',
      'carbon_history_logs.history_time':'history_time',
    },
    TYPE_VALIDATION: {
        'carbon_history_logs.history_uuid': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_history_logs.history_table': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_history_logs.history_type': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_history_logs.history_data': { 
            MYSQL_TYPE: 'json', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_history_logs.history_original_query': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '1024', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_history_logs.history_time': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iLocation_References {
      'entity_reference'?: string;
      'location_reference'?: string;
      'location_time'?: string;
}

interface iDefineLocation_References {
      'ENTITY_REFERENCE': string;
      'LOCATION_REFERENCE': string;
      'LOCATION_TIME': string;
}

export const location_references : C6RestfulModel & iDefineLocation_References = {
    TABLE_NAME:'location_references',
    ENTITY_REFERENCE: 'carbon_location_references.entity_reference',
    LOCATION_REFERENCE: 'carbon_location_references.location_reference',
    LOCATION_TIME: 'carbon_location_references.location_time',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_location_references.entity_reference':'entity_reference',
      'carbon_location_references.location_reference':'location_reference',
      'carbon_location_references.location_time':'location_time',
    },
    TYPE_VALIDATION: {
        'carbon_location_references.entity_reference': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_location_references.location_reference': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_location_references.location_time': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iLocations {
      'entity_id'?: string;
      'latitude'?: string;
      'longitude'?: string;
      'street'?: string;
      'city'?: string;
      'state'?: string;
      'elevation'?: string;
      'zip'?: number;
}

interface iDefineLocations {
      'ENTITY_ID': string;
      'LATITUDE': string;
      'LONGITUDE': string;
      'STREET': string;
      'CITY': string;
      'STATE': string;
      'ELEVATION': string;
      'ZIP': string;
}

export const locations : C6RestfulModel & iDefineLocations = {
    TABLE_NAME:'locations',
    ENTITY_ID: 'carbon_locations.entity_id',
    LATITUDE: 'carbon_locations.latitude',
    LONGITUDE: 'carbon_locations.longitude',
    STREET: 'carbon_locations.street',
    CITY: 'carbon_locations.city',
    STATE: 'carbon_locations.state',
    ELEVATION: 'carbon_locations.elevation',
    ZIP: 'carbon_locations.zip',
    PRIMARY: [
        'carbon_locations.entity_id',
    ],
    COLUMNS: {
      'carbon_locations.entity_id':'entity_id',
      'carbon_locations.latitude':'latitude',
      'carbon_locations.longitude':'longitude',
      'carbon_locations.street':'street',
      'carbon_locations.city':'city',
      'carbon_locations.state':'state',
      'carbon_locations.elevation':'elevation',
      'carbon_locations.zip':'zip',
    },
    TYPE_VALIDATION: {
        'carbon_locations.entity_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_locations.latitude': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.longitude': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.street': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.city': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '40', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.state': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '10', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.elevation': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '40', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_locations.zip': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iPhotos {
      'parent_id'?: string;
      'photo_id'?: string;
      'user_id'?: string;
      'photo_path'?: string;
      'photo_description'?: string;
}

interface iDefinePhotos {
      'PARENT_ID': string;
      'PHOTO_ID': string;
      'USER_ID': string;
      'PHOTO_PATH': string;
      'PHOTO_DESCRIPTION': string;
}

export const photos : C6RestfulModel & iDefinePhotos = {
    TABLE_NAME:'photos',
    PARENT_ID: 'carbon_photos.parent_id',
    PHOTO_ID: 'carbon_photos.photo_id',
    USER_ID: 'carbon_photos.user_id',
    PHOTO_PATH: 'carbon_photos.photo_path',
    PHOTO_DESCRIPTION: 'carbon_photos.photo_description',
    PRIMARY: [
        'carbon_photos.parent_id',
    ],
    COLUMNS: {
      'carbon_photos.parent_id':'parent_id',
      'carbon_photos.photo_id':'photo_id',
      'carbon_photos.user_id':'user_id',
      'carbon_photos.photo_path':'photo_path',
      'carbon_photos.photo_description':'photo_description',
    },
    TYPE_VALIDATION: {
        'carbon_photos.parent_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_photos.photo_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_photos.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_photos.photo_path': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_photos.photo_description': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iReports {
      'log_level'?: string;
      'report'?: string;
      'date'?: string;
      'call_trace'?: string;
}

interface iDefineReports {
      'LOG_LEVEL': string;
      'REPORT': string;
      'DATE': string;
      'CALL_TRACE': string;
}

export const reports : C6RestfulModel & iDefineReports = {
    TABLE_NAME:'reports',
    LOG_LEVEL: 'carbon_reports.log_level',
    REPORT: 'carbon_reports.report',
    DATE: 'carbon_reports.date',
    CALL_TRACE: 'carbon_reports.call_trace',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_reports.log_level':'log_level',
      'carbon_reports.report':'report',
      'carbon_reports.date':'date',
      'carbon_reports.call_trace':'call_trace',
    },
    TYPE_VALIDATION: {
        'carbon_reports.log_level': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_reports.report': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_reports.date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_reports.call_trace': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUser_Followers {
      'follower_table_id'?: string;
      'follows_user_id'?: string;
      'user_id'?: string;
}

interface iDefineUser_Followers {
      'FOLLOWER_TABLE_ID': string;
      'FOLLOWS_USER_ID': string;
      'USER_ID': string;
}

export const user_followers : C6RestfulModel & iDefineUser_Followers = {
    TABLE_NAME:'user_followers',
    FOLLOWER_TABLE_ID: 'carbon_user_followers.follower_table_id',
    FOLLOWS_USER_ID: 'carbon_user_followers.follows_user_id',
    USER_ID: 'carbon_user_followers.user_id',
    PRIMARY: [
        'carbon_user_followers.follower_table_id',
    ],
    COLUMNS: {
      'carbon_user_followers.follower_table_id':'follower_table_id',
      'carbon_user_followers.follows_user_id':'follows_user_id',
      'carbon_user_followers.user_id':'user_id',
    },
    TYPE_VALIDATION: {
        'carbon_user_followers.follower_table_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_followers.follows_user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_followers.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUser_Groups {
      'group_id'?: string;
      'user_id'?: string;
}

interface iDefineUser_Groups {
      'GROUP_ID': string;
      'USER_ID': string;
}

export const user_groups : C6RestfulModel & iDefineUser_Groups = {
    TABLE_NAME:'user_groups',
    GROUP_ID: 'carbon_user_groups.group_id',
    USER_ID: 'carbon_user_groups.user_id',
    PRIMARY: [
    ],
    COLUMNS: {
      'carbon_user_groups.group_id':'group_id',
      'carbon_user_groups.user_id':'user_id',
    },
    TYPE_VALIDATION: {
        'carbon_user_groups.group_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_groups.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUser_Messages {
      'message_id'?: string;
      'from_user_id'?: string;
      'to_user_id'?: string;
      'message'?: string;
      'message_read'?: number;
      'creation_date'?: string;
}

interface iDefineUser_Messages {
      'MESSAGE_ID': string;
      'FROM_USER_ID': string;
      'TO_USER_ID': string;
      'MESSAGE': string;
      'MESSAGE_READ': string;
      'CREATION_DATE': string;
}

export const user_messages : C6RestfulModel & iDefineUser_Messages = {
    TABLE_NAME:'user_messages',
    MESSAGE_ID: 'carbon_user_messages.message_id',
    FROM_USER_ID: 'carbon_user_messages.from_user_id',
    TO_USER_ID: 'carbon_user_messages.to_user_id',
    MESSAGE: 'carbon_user_messages.message',
    MESSAGE_READ: 'carbon_user_messages.message_read',
    CREATION_DATE: 'carbon_user_messages.creation_date',
    PRIMARY: [
        'carbon_user_messages.message_id',
    ],
    COLUMNS: {
      'carbon_user_messages.message_id':'message_id',
      'carbon_user_messages.from_user_id':'from_user_id',
      'carbon_user_messages.to_user_id':'to_user_id',
      'carbon_user_messages.message':'message',
      'carbon_user_messages.message_read':'message_read',
      'carbon_user_messages.creation_date':'creation_date',
    },
    TYPE_VALIDATION: {
        'carbon_user_messages.message_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_messages.from_user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_messages.to_user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_messages.message': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_messages.message_read': { 
            MYSQL_TYPE: 'tinyint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_messages.creation_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUser_Sessions {
      'user_id'?: string;
      'user_ip'?: string;
      'session_id'?: string;
      'session_expires'?: string;
      'session_data'?: string;
      'user_online_status'?: number;
}

interface iDefineUser_Sessions {
      'USER_ID': string;
      'USER_IP': string;
      'SESSION_ID': string;
      'SESSION_EXPIRES': string;
      'SESSION_DATA': string;
      'USER_ONLINE_STATUS': string;
}

export const user_sessions : C6RestfulModel & iDefineUser_Sessions = {
    TABLE_NAME:'user_sessions',
    USER_ID: 'carbon_user_sessions.user_id',
    USER_IP: 'carbon_user_sessions.user_ip',
    SESSION_ID: 'carbon_user_sessions.session_id',
    SESSION_EXPIRES: 'carbon_user_sessions.session_expires',
    SESSION_DATA: 'carbon_user_sessions.session_data',
    USER_ONLINE_STATUS: 'carbon_user_sessions.user_online_status',
    PRIMARY: [
        'carbon_user_sessions.session_id',
    ],
    COLUMNS: {
      'carbon_user_sessions.user_id':'user_id',
      'carbon_user_sessions.user_ip':'user_ip',
      'carbon_user_sessions.session_id':'session_id',
      'carbon_user_sessions.session_expires':'session_expires',
      'carbon_user_sessions.session_data':'session_data',
      'carbon_user_sessions.user_online_status':'user_online_status',
    },
    TYPE_VALIDATION: {
        'carbon_user_sessions.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_sessions.user_ip': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_sessions.session_id': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_sessions.session_expires': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_sessions.session_data': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_sessions.user_online_status': { 
            MYSQL_TYPE: 'tinyint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUser_Tasks {
      'task_id'?: string;
      'user_id'?: string;
      'from_id'?: string;
      'task_name'?: string;
      'task_description'?: string;
      'percent_complete'?: number;
      'start_date'?: string;
      'end_date'?: string;
}

interface iDefineUser_Tasks {
      'TASK_ID': string;
      'USER_ID': string;
      'FROM_ID': string;
      'TASK_NAME': string;
      'TASK_DESCRIPTION': string;
      'PERCENT_COMPLETE': string;
      'START_DATE': string;
      'END_DATE': string;
}

export const user_tasks : C6RestfulModel & iDefineUser_Tasks = {
    TABLE_NAME:'user_tasks',
    TASK_ID: 'carbon_user_tasks.task_id',
    USER_ID: 'carbon_user_tasks.user_id',
    FROM_ID: 'carbon_user_tasks.from_id',
    TASK_NAME: 'carbon_user_tasks.task_name',
    TASK_DESCRIPTION: 'carbon_user_tasks.task_description',
    PERCENT_COMPLETE: 'carbon_user_tasks.percent_complete',
    START_DATE: 'carbon_user_tasks.start_date',
    END_DATE: 'carbon_user_tasks.end_date',
    PRIMARY: [
        'carbon_user_tasks.task_id',
    ],
    COLUMNS: {
      'carbon_user_tasks.task_id':'task_id',
      'carbon_user_tasks.user_id':'user_id',
      'carbon_user_tasks.from_id':'from_id',
      'carbon_user_tasks.task_name':'task_name',
      'carbon_user_tasks.task_description':'task_description',
      'carbon_user_tasks.percent_complete':'percent_complete',
      'carbon_user_tasks.start_date':'start_date',
      'carbon_user_tasks.end_date':'end_date',
    },
    TYPE_VALIDATION: {
        'carbon_user_tasks.task_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_tasks.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_tasks.from_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_tasks.task_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '40', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_user_tasks.task_description': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_tasks.percent_complete': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_tasks.start_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_user_tasks.end_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export interface  iUsers {
      'user_username'?: string;
      'user_password'?: string;
      'user_id'?: string;
      'user_type'?: string;
      'user_sport'?: string;
      'user_session_id'?: string;
      'user_facebook_id'?: string;
      'user_first_name'?: string;
      'user_last_name'?: string;
      'user_profile_pic'?: string;
      'user_profile_uri'?: string;
      'user_cover_photo'?: string;
      'user_birthday'?: string;
      'user_gender'?: string;
      'user_about_me'?: string;
      'user_rank'?: number;
      'user_email'?: string;
      'user_email_code'?: string;
      'user_email_confirmed'?: number;
      'user_generated_string'?: string;
      'user_membership'?: number;
      'user_deactivated'?: number;
      'user_last_login'?: string;
      'user_ip'?: string;
      'user_education_history'?: string;
      'user_location'?: string;
      'user_creation_date'?: string;
}

interface iDefineUsers {
      'USER_USERNAME': string;
      'USER_PASSWORD': string;
      'USER_ID': string;
      'USER_TYPE': string;
      'USER_SPORT': string;
      'USER_SESSION_ID': string;
      'USER_FACEBOOK_ID': string;
      'USER_FIRST_NAME': string;
      'USER_LAST_NAME': string;
      'USER_PROFILE_PIC': string;
      'USER_PROFILE_URI': string;
      'USER_COVER_PHOTO': string;
      'USER_BIRTHDAY': string;
      'USER_GENDER': string;
      'USER_ABOUT_ME': string;
      'USER_RANK': string;
      'USER_EMAIL': string;
      'USER_EMAIL_CODE': string;
      'USER_EMAIL_CONFIRMED': string;
      'USER_GENERATED_STRING': string;
      'USER_MEMBERSHIP': string;
      'USER_DEACTIVATED': string;
      'USER_LAST_LOGIN': string;
      'USER_IP': string;
      'USER_EDUCATION_HISTORY': string;
      'USER_LOCATION': string;
      'USER_CREATION_DATE': string;
}

export const users : C6RestfulModel & iDefineUsers = {
    TABLE_NAME:'users',
    USER_USERNAME: 'carbon_users.user_username',
    USER_PASSWORD: 'carbon_users.user_password',
    USER_ID: 'carbon_users.user_id',
    USER_TYPE: 'carbon_users.user_type',
    USER_SPORT: 'carbon_users.user_sport',
    USER_SESSION_ID: 'carbon_users.user_session_id',
    USER_FACEBOOK_ID: 'carbon_users.user_facebook_id',
    USER_FIRST_NAME: 'carbon_users.user_first_name',
    USER_LAST_NAME: 'carbon_users.user_last_name',
    USER_PROFILE_PIC: 'carbon_users.user_profile_pic',
    USER_PROFILE_URI: 'carbon_users.user_profile_uri',
    USER_COVER_PHOTO: 'carbon_users.user_cover_photo',
    USER_BIRTHDAY: 'carbon_users.user_birthday',
    USER_GENDER: 'carbon_users.user_gender',
    USER_ABOUT_ME: 'carbon_users.user_about_me',
    USER_RANK: 'carbon_users.user_rank',
    USER_EMAIL: 'carbon_users.user_email',
    USER_EMAIL_CODE: 'carbon_users.user_email_code',
    USER_EMAIL_CONFIRMED: 'carbon_users.user_email_confirmed',
    USER_GENERATED_STRING: 'carbon_users.user_generated_string',
    USER_MEMBERSHIP: 'carbon_users.user_membership',
    USER_DEACTIVATED: 'carbon_users.user_deactivated',
    USER_LAST_LOGIN: 'carbon_users.user_last_login',
    USER_IP: 'carbon_users.user_ip',
    USER_EDUCATION_HISTORY: 'carbon_users.user_education_history',
    USER_LOCATION: 'carbon_users.user_location',
    USER_CREATION_DATE: 'carbon_users.user_creation_date',
    PRIMARY: [
        'carbon_users.user_id',
    ],
    COLUMNS: {
      'carbon_users.user_username':'user_username',
      'carbon_users.user_password':'user_password',
      'carbon_users.user_id':'user_id',
      'carbon_users.user_type':'user_type',
      'carbon_users.user_sport':'user_sport',
      'carbon_users.user_session_id':'user_session_id',
      'carbon_users.user_facebook_id':'user_facebook_id',
      'carbon_users.user_first_name':'user_first_name',
      'carbon_users.user_last_name':'user_last_name',
      'carbon_users.user_profile_pic':'user_profile_pic',
      'carbon_users.user_profile_uri':'user_profile_uri',
      'carbon_users.user_cover_photo':'user_cover_photo',
      'carbon_users.user_birthday':'user_birthday',
      'carbon_users.user_gender':'user_gender',
      'carbon_users.user_about_me':'user_about_me',
      'carbon_users.user_rank':'user_rank',
      'carbon_users.user_email':'user_email',
      'carbon_users.user_email_code':'user_email_code',
      'carbon_users.user_email_confirmed':'user_email_confirmed',
      'carbon_users.user_generated_string':'user_generated_string',
      'carbon_users.user_membership':'user_membership',
      'carbon_users.user_deactivated':'user_deactivated',
      'carbon_users.user_last_login':'user_last_login',
      'carbon_users.user_ip':'user_ip',
      'carbon_users.user_education_history':'user_education_history',
      'carbon_users.user_location':'user_location',
      'carbon_users.user_creation_date':'user_creation_date',
    },
    TYPE_VALIDATION: {
        'carbon_users.user_username': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_password': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_id': { 
            MYSQL_TYPE: 'binary', 
            MAX_LENGTH: '16', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_type': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_sport': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_session_id': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_facebook_id': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_first_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '25', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_last_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '25', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_profile_pic': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_profile_uri': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_cover_photo': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_birthday': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '9', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_gender': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '25', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_about_me': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_rank': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_email': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '50', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_email_code': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '225', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_email_confirmed': { 
            MYSQL_TYPE: 'tinyint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_generated_string': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_membership': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_deactivated': { 
            MYSQL_TYPE: 'tinyint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_last_login': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_ip': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_users.user_education_history': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_location': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_users.user_creation_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

}

  

export const TABLES = {
    
  carbons: carbons,
  comments: comments,
  documentation: documentation,
  feature_group_references: feature_group_references,
  features: features,
  group_references: group_references,
  groups: groups,
  history_logs: history_logs,
  location_references: location_references,
  locations: locations,
  photos: photos,
  reports: reports,
  user_followers: user_followers,
  user_groups: user_groups,
  user_messages: user_messages,
  user_sessions: user_sessions,
  user_tasks: user_tasks,
  users: users,
};

export const C6 : { TABLES: { [key: string]: (C6RestfulModel & { [key: string]: any }) } }
                & { [key: string]: any } = {
        // try to 1=1 match the Rest abstract class
    ADDDATE: 'ADDDATE',
    ADDTIME: 'ADDTIME',
    AS: 'AS',
    ASC: 'ASC',
    
    BETWEEN: 'BETWEEN',
    
    CONCAT: 'CONCAT',
    CONVERT_TZ: 'CONVERT_TZ',
    COUNT: 'COUNT',
    COUNT_ALL: 'COUNT_ALL',
    CURRENT_DATE: 'CURRENT_DATE',
    CURRENT_TIMESTAMP: 'CURRENT_TIMESTAMP',
    
    DAY: 'DAY',
    DAY_HOUR: 'DAY_HOUR',
    DAY_MICROSECOND: 'DAY_MICROSECOND',
    DAY_MINUTE: 'DAY_MINUTE',
    DAY_SECOND: 'DAY_SECOND',
    DAYNAME: 'DAYNAME',
    DAYOFMONTH: 'DAYOFMONTH',
    DAYOFWEEK: 'DAYOFWEEK',
    DAYOFYEAR: 'DAYOFYEAR',
    DATE: 'DATE',
    DATE_ADD: 'DATE_ADD',
    DATEDIFF: 'DATEDIFF',
    DATE_SUB: 'DATE_SUB',
    DATE_FORMAT: 'DATE_FORMAT',
    DESC: 'DESC',
    DISTINCT: 'DISTINCT',
    
    EXTRACT: 'EXTRACT',
    EQUAL: '=',
    EQUAL_NULL_SAFE: '<=>',
    
    FALSE: 'FALSE',
    FULL_OUTER: 'FULL_OUTER',
    FROM_DAYS: 'FROM_DAYS',
    FROM_UNIXTIME: 'FROM_UNIXTIME',
    
    GET_FORMAT: 'GET_FORMAT',
    GREATER_THAN: '>',
    GROUP_BY: 'GROUP_BY',
    GROUP_CONCAT: 'GROUP_CONCAT',
    GREATER_THAN_OR_EQUAL_TO: '>=',
    
    HAVING: 'HAVING',
    HEX: 'HEX',
    HOUR: 'HOUR',
    HOUR_MICROSECOND: 'HOUR_MICROSECOND',
    HOUR_SECOND: 'HOUR_SECOND',
    HOUR_MINUTE: 'HOUR_MINUTE',
    
    IN: 'IN',
    IS: 'IS',
    IS_NOT: 'IS_NOT',
    INNER: 'INNER',
    INTERVAL: 'INTERVAL',
    
    JOIN: 'JOIN',
    
    LEFT: 'LEFT',
    LEFT_OUTER: 'LEFT_OUTER',
    LESS_THAN: '<',
    LESS_THAN_OR_EQUAL_TO: '<=',
    LIKE: 'LIKE',
    LIMIT: 'LIMIT',
    LOCALTIME: 'LOCALTIME',
    LOCALTIMESTAMP: 'LOCALTIMESTAMP',
    
    MAKEDATE: 'MAKEDATE',
    MAKETIME: 'MAKETIME',
    MONTHNAME: 'MONTHNAME',
    MICROSECOND: 'MICROSECOND',
    MINUTE: 'MINUTE',
    MINUTE_MICROSECOND: 'MINUTE_MICROSECOND',
    MINUTE_SECOND: 'MINUTE_SECOND',
    MIN: 'MIN',
    MAX: 'MAX',
    MONTH: 'MONTH',
    
    NOT_LIKE: 'NOT_LIKE',
    NOT_EQUAL: '<>',
    NOT_IN: 'NOT_IN',
    NOW: 'NOW',
    NULL: 'NULL',
    
    ORDER: 'ORDER',
    
    PAGE: 'PAGE',
    PAGINATION: 'PAGINATION',
    RIGHT_OUTER: 'RIGHT_OUTER',
    
    SECOND: 'SECOND',
    SECOND_MICROSECOND: 'SECOND_MICROSECOND',
    SELECT: 'SELECT',
    STR_TO_DATE: 'STR_TO_DATE',
    SUBDATE: 'SUBDATE',
    SUBTIME: 'SUBTIME',
    SUM: 'SUM',
    SYSDATE: 'SYSDATE',
    
    TIME: 'TIME',
    TIME_FORMAT: 'TIME_FORMAT',
    TIME_TO_SEC: 'TIME_TO_SEC',
    TIMEDIFF: 'TIMEDIFF',
    TIMESTAMP: 'TIMESTAMP',
    TIMESTAMPADD: 'TIMESTAMPADD',
    TIMESTAMPDIFF: 'TIMESTAMPDIFF',
    TO_DAYS: 'TO_DAYS',
    TO_SECONDS: 'TO_SECONDS',
    TRANSACTION_TIMESTAMP: 'TRANSACTION_TIMESTAMP',
    TRUE: 'TRUE',
    
    UNIX_TIMESTAMP: 'UNIX_TIMESTAMP',
    UNKNOWN: 'UNKNOWN',
    UPDATE: 'UPDATE',
    UNHEX: 'UNHEX',
    UTC_DATE: 'UNHEX',
    UTC_TIME: 'UNHEX',
    UTC_TIMESTAMP: 'UNHEX',
    
    WHERE: 'WHERE',
    WEEKDAY: 'WEEKDAY',
    WEEKOFYEAR: 'WEEKOFYEAR',
    
    YEARWEEK: 'YEARWEEK',
   
    
    // carbon identifiers
    DEPENDANT_ON_ENTITY: 'DEPENDANT_ON_ENTITY',
   
    // PHP validation
    OPTIONS: 'OPTIONS',
    GET: 'GET',
    POST: 'POST',
    PUT: 'PUT',
    REPLACE: 'REPLACE INTO',
    DELETE: 'DELETE',
    REST_REQUEST_PREPROCESS_CALLBACKS: 'PREPROCESS',
    PREPROCESS: 'PREPROCESS',
    REST_REQUEST_FINNISH_CALLBACKS: 'FINISH',
    FINISH: 'FINISH',
    VALIDATE_C6_ENTITY_ID_REGEX: '#^([a-fA-F0-9]{20,35})$#',

    TABLES: TABLES,
    ...TABLES
    
};

export const COLUMNS = {
    
'carbon_carbons.entity_pk':'entity_pk',
'carbon_carbons.entity_fk':'entity_fk',
'carbon_carbons.entity_tag':'entity_tag',

'carbon_comments.parent_id':'parent_id',
'carbon_comments.comment_id':'comment_id',
'carbon_comments.user_id':'user_id',
'carbon_comments.comment':'comment',

'carbon_documentation.documentation_uri':'documentation_uri',
'carbon_documentation.documentation_data':'documentation_data',
'carbon_documentation.documentation_version':'documentation_version',
'carbon_documentation.documentation_active':'documentation_active',

'carbon_feature_group_references.feature_entity_id':'feature_entity_id',
'carbon_feature_group_references.group_entity_id':'group_entity_id',

'carbon_features.feature_entity_id':'feature_entity_id',
'carbon_features.feature_code':'feature_code',
'carbon_features.feature_creation_date':'feature_creation_date',

'carbon_group_references.group_id':'group_id',
'carbon_group_references.allowed_to_grant_group_id':'allowed_to_grant_group_id',

'carbon_groups.group_name':'group_name',
'carbon_groups.entity_id':'entity_id',
'carbon_groups.created_by':'created_by',
'carbon_groups.creation_date':'creation_date',

'carbon_history_logs.history_uuid':'history_uuid',
'carbon_history_logs.history_table':'history_table',
'carbon_history_logs.history_type':'history_type',
'carbon_history_logs.history_data':'history_data',
'carbon_history_logs.history_original_query':'history_original_query',
'carbon_history_logs.history_time':'history_time',

'carbon_location_references.entity_reference':'entity_reference',
'carbon_location_references.location_reference':'location_reference',
'carbon_location_references.location_time':'location_time',

'carbon_locations.entity_id':'entity_id',
'carbon_locations.latitude':'latitude',
'carbon_locations.longitude':'longitude',
'carbon_locations.street':'street',
'carbon_locations.city':'city',
'carbon_locations.state':'state',
'carbon_locations.elevation':'elevation',
'carbon_locations.zip':'zip',

'carbon_photos.parent_id':'parent_id',
'carbon_photos.photo_id':'photo_id',
'carbon_photos.user_id':'user_id',
'carbon_photos.photo_path':'photo_path',
'carbon_photos.photo_description':'photo_description',

'carbon_reports.log_level':'log_level',
'carbon_reports.report':'report',
'carbon_reports.date':'date',
'carbon_reports.call_trace':'call_trace',

'carbon_user_followers.follower_table_id':'follower_table_id',
'carbon_user_followers.follows_user_id':'follows_user_id',
'carbon_user_followers.user_id':'user_id',

'carbon_user_groups.group_id':'group_id',
'carbon_user_groups.user_id':'user_id',

'carbon_user_messages.message_id':'message_id',
'carbon_user_messages.from_user_id':'from_user_id',
'carbon_user_messages.to_user_id':'to_user_id',
'carbon_user_messages.message':'message',
'carbon_user_messages.message_read':'message_read',
'carbon_user_messages.creation_date':'creation_date',

'carbon_user_sessions.user_id':'user_id',
'carbon_user_sessions.user_ip':'user_ip',
'carbon_user_sessions.session_id':'session_id',
'carbon_user_sessions.session_expires':'session_expires',
'carbon_user_sessions.session_data':'session_data',
'carbon_user_sessions.user_online_status':'user_online_status',

'carbon_user_tasks.task_id':'task_id',
'carbon_user_tasks.user_id':'user_id',
'carbon_user_tasks.from_id':'from_id',
'carbon_user_tasks.task_name':'task_name',
'carbon_user_tasks.task_description':'task_description',
'carbon_user_tasks.percent_complete':'percent_complete',
'carbon_user_tasks.start_date':'start_date',
'carbon_user_tasks.end_date':'end_date',

'carbon_users.user_username':'user_username',
'carbon_users.user_password':'user_password',
'carbon_users.user_id':'user_id',
'carbon_users.user_type':'user_type',
'carbon_users.user_sport':'user_sport',
'carbon_users.user_session_id':'user_session_id',
'carbon_users.user_facebook_id':'user_facebook_id',
'carbon_users.user_first_name':'user_first_name',
'carbon_users.user_last_name':'user_last_name',
'carbon_users.user_profile_pic':'user_profile_pic',
'carbon_users.user_profile_uri':'user_profile_uri',
'carbon_users.user_cover_photo':'user_cover_photo',
'carbon_users.user_birthday':'user_birthday',
'carbon_users.user_gender':'user_gender',
'carbon_users.user_about_me':'user_about_me',
'carbon_users.user_rank':'user_rank',
'carbon_users.user_email':'user_email',
'carbon_users.user_email_code':'user_email_code',
'carbon_users.user_email_confirmed':'user_email_confirmed',
'carbon_users.user_generated_string':'user_generated_string',
'carbon_users.user_membership':'user_membership',
'carbon_users.user_deactivated':'user_deactivated',
'carbon_users.user_last_login':'user_last_login',
'carbon_users.user_ip':'user_ip',
'carbon_users.user_education_history':'user_education_history',
'carbon_users.user_location':'user_location',
'carbon_users.user_creation_date':'user_creation_date',

};

export type RestTableInterfaces = iCarbons
	| iComments
	| iDocumentation
	| iFeature_Group_References
	| iFeatures
	| iGroup_References
	| iGroups
	| iHistory_Logs
	| iLocation_References
	| iLocations
	| iPhotos
	| iReports
	| iUser_Followers
	| iUser_Groups
	| iUser_Messages
	| iUser_Sessions
	| iUser_Tasks
	| iUsers;


export type tStatefulApiData<T> = T[] | undefined | null;


// this refers to the value types of the keys above, aka values in the state
export interface iRestfulObjectArrayTypes {
    
  carbons: tStatefulApiData<iCarbons>,
  comments: tStatefulApiData<iComments>,
  documentation: tStatefulApiData<iDocumentation>,
  feature_group_references: tStatefulApiData<iFeature_Group_References>,
  features: tStatefulApiData<iFeatures>,
  group_references: tStatefulApiData<iGroup_References>,
  groups: tStatefulApiData<iGroups>,
  history_logs: tStatefulApiData<iHistory_Logs>,
  location_references: tStatefulApiData<iLocation_References>,
  locations: tStatefulApiData<iLocations>,
  photos: tStatefulApiData<iPhotos>,
  reports: tStatefulApiData<iReports>,
  user_followers: tStatefulApiData<iUser_Followers>,
  user_groups: tStatefulApiData<iUser_Groups>,
  user_messages: tStatefulApiData<iUser_Messages>,
  user_sessions: tStatefulApiData<iUser_Sessions>,
  user_tasks: tStatefulApiData<iUser_Tasks>,
  users: tStatefulApiData<iUsers>,
}

export const initialRestfulObjectArrayTypes: iRestfulObjectArrayTypes = {
    
  carbons: undefined,
  comments: undefined,
  documentation: undefined,
  feature_group_references: undefined,
  features: undefined,
  group_references: undefined,
  groups: undefined,
  history_logs: undefined,
  location_references: undefined,
  locations: undefined,
  photos: undefined,
  reports: undefined,
  user_followers: undefined,
  user_groups: undefined,
  user_messages: undefined,
  user_sessions: undefined,
  user_tasks: undefined,
  users: undefined,
};

export type tRestfulObjectArrayKeys = keyof iRestfulObjectArrayTypes

export type tRestfulObjectArrayValues = iRestfulObjectArrayTypes[tRestfulObjectArrayKeys];

// @ts-ignore
export type tRestfulObjectValues = tRestfulObjectArrayValues[number];


