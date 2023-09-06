

const C6 = {
    
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

    
    

    carbons: {
    TABLE_NAME:'carbons',
    ENTITY_PK: 'carbon_carbons.entity_pk',
    ENTITY_FK: 'carbon_carbons.entity_fk',
    ENTITY_TAG: 'carbon_carbons.entity_tag',
    PRIMARY: [
        'carbon_carbons.entity_pk',
    ],
    PRIMARY_SHORT: [
        'entity_pk',
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

  },

    comments: {
    TABLE_NAME:'comments',
    PARENT_ID: 'carbon_comments.parent_id',
    COMMENT_ID: 'carbon_comments.comment_id',
    USER_ID: 'carbon_comments.user_id',
    COMMENT: 'carbon_comments.comment',
    PRIMARY: [
        'carbon_comments.comment_id',
    ],
    PRIMARY_SHORT: [
        'comment_id',
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

  },

    documentation: {
    TABLE_NAME:'documentation',
    DOCUMENTATION_URI: 'carbon_documentation.documentation_uri',
    DOCUMENTATION_DATA: 'carbon_documentation.documentation_data',
    DOCUMENTATION_VERSION: 'carbon_documentation.documentation_version',
    DOCUMENTATION_ACTIVE: 'carbon_documentation.documentation_active',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    feature_group_references: {
    TABLE_NAME:'feature_group_references',
    FEATURE_ENTITY_ID: 'carbon_feature_group_references.feature_entity_id',
    GROUP_ENTITY_ID: 'carbon_feature_group_references.group_entity_id',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    features: {
    TABLE_NAME:'features',
    FEATURE_ENTITY_ID: 'carbon_features.feature_entity_id',
    FEATURE_CODE: 'carbon_features.feature_code',
    FEATURE_CREATION_DATE: 'carbon_features.feature_creation_date',
    PRIMARY: [
        'carbon_features.feature_entity_id',
    ],
    PRIMARY_SHORT: [
        'feature_entity_id',
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

  },

    group_references: {
    TABLE_NAME:'group_references',
    GROUP_ID: 'carbon_group_references.group_id',
    ALLOWED_TO_GRANT_GROUP_ID: 'carbon_group_references.allowed_to_grant_group_id',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    groups: {
    TABLE_NAME:'groups',
    GROUP_NAME: 'carbon_groups.group_name',
    ENTITY_ID: 'carbon_groups.entity_id',
    CREATED_BY: 'carbon_groups.created_by',
    CREATION_DATE: 'carbon_groups.creation_date',
    PRIMARY: [
        'carbon_groups.entity_id',
    ],
    PRIMARY_SHORT: [
        'entity_id',
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

  },

    history_logs: {
    TABLE_NAME:'history_logs',
    HISTORY_UUID: 'carbon_history_logs.history_uuid',
    HISTORY_TABLE: 'carbon_history_logs.history_table',
    HISTORY_TYPE: 'carbon_history_logs.history_type',
    HISTORY_DATA: 'carbon_history_logs.history_data',
    HISTORY_ORIGINAL_QUERY: 'carbon_history_logs.history_original_query',
    HISTORY_TIME: 'carbon_history_logs.history_time',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    location_references: {
    TABLE_NAME:'location_references',
    ENTITY_REFERENCE: 'carbon_location_references.entity_reference',
    LOCATION_REFERENCE: 'carbon_location_references.location_reference',
    LOCATION_TIME: 'carbon_location_references.location_time',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    locations: {
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
    PRIMARY_SHORT: [
        'entity_id',
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

  },

    photos: {
    TABLE_NAME:'photos',
    PARENT_ID: 'carbon_photos.parent_id',
    PHOTO_ID: 'carbon_photos.photo_id',
    USER_ID: 'carbon_photos.user_id',
    PHOTO_PATH: 'carbon_photos.photo_path',
    PHOTO_DESCRIPTION: 'carbon_photos.photo_description',
    PRIMARY: [
        'carbon_photos.parent_id',
    ],
    PRIMARY_SHORT: [
        'parent_id',
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

  },

    reports: {
    TABLE_NAME:'reports',
    LOG_LEVEL: 'carbon_reports.log_level',
    REPORT: 'carbon_reports.report',
    DATE: 'carbon_reports.date',
    CALL_TRACE: 'carbon_reports.call_trace',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    user_followers: {
    TABLE_NAME:'user_followers',
    FOLLOWER_TABLE_ID: 'carbon_user_followers.follower_table_id',
    FOLLOWS_USER_ID: 'carbon_user_followers.follows_user_id',
    USER_ID: 'carbon_user_followers.user_id',
    PRIMARY: [
        'carbon_user_followers.follower_table_id',
    ],
    PRIMARY_SHORT: [
        'follower_table_id',
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

  },

    user_groups: {
    TABLE_NAME:'user_groups',
    GROUP_ID: 'carbon_user_groups.group_id',
    USER_ID: 'carbon_user_groups.user_id',
    PRIMARY: [
    ],
    PRIMARY_SHORT: [
        
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

  },

    user_messages: {
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
    PRIMARY_SHORT: [
        'message_id',
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

  },

    user_sessions: {
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
    PRIMARY_SHORT: [
        'session_id',
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
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '80', 
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

  },

    user_tasks: {
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
    PRIMARY_SHORT: [
        'task_id',
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

  },

    users: {
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
    PRIMARY_SHORT: [
        'user_id',
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

  },

    wp_commentmeta: {
    TABLE_NAME:'wp_commentmeta',
    META_ID: 'carbon_wp_commentmeta.meta_id',
    COMMENT_ID: 'carbon_wp_commentmeta.comment_id',
    META_KEY: 'carbon_wp_commentmeta.meta_key',
    META_VALUE: 'carbon_wp_commentmeta.meta_value',
    PRIMARY: [
        'carbon_wp_commentmeta.meta_id',
    ],
    PRIMARY_SHORT: [
        'meta_id',
    ],
    COLUMNS: {
        'carbon_wp_commentmeta.meta_id':'meta_id',
        'carbon_wp_commentmeta.comment_id':'comment_id',
        'carbon_wp_commentmeta.meta_key':'meta_key',
        'carbon_wp_commentmeta.meta_value':'meta_value',
    },
    TYPE_VALIDATION: {
        'carbon_wp_commentmeta.meta_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_commentmeta.comment_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_commentmeta.meta_key': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_commentmeta.meta_value': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_comments: {
    TABLE_NAME:'wp_comments',
    COMMENT_ID: 'carbon_wp_comments.comment_ID',
    COMMENT_POST_ID: 'carbon_wp_comments.comment_post_ID',
    COMMENT_AUTHOR: 'carbon_wp_comments.comment_author',
    COMMENT_AUTHOR_EMAIL: 'carbon_wp_comments.comment_author_email',
    COMMENT_AUTHOR_URL: 'carbon_wp_comments.comment_author_url',
    COMMENT_AUTHOR_IP: 'carbon_wp_comments.comment_author_IP',
    COMMENT_DATE: 'carbon_wp_comments.comment_date',
    COMMENT_DATE_GMT: 'carbon_wp_comments.comment_date_gmt',
    COMMENT_CONTENT: 'carbon_wp_comments.comment_content',
    COMMENT_KARMA: 'carbon_wp_comments.comment_karma',
    COMMENT_APPROVED: 'carbon_wp_comments.comment_approved',
    COMMENT_AGENT: 'carbon_wp_comments.comment_agent',
    COMMENT_TYPE: 'carbon_wp_comments.comment_type',
    COMMENT_PARENT: 'carbon_wp_comments.comment_parent',
    USER_ID: 'carbon_wp_comments.user_id',
    PRIMARY: [
        'carbon_wp_comments.comment_ID',
    ],
    PRIMARY_SHORT: [
        'comment_ID',
    ],
    COLUMNS: {
        'carbon_wp_comments.comment_ID':'comment_ID',
        'carbon_wp_comments.comment_post_ID':'comment_post_ID',
        'carbon_wp_comments.comment_author':'comment_author',
        'carbon_wp_comments.comment_author_email':'comment_author_email',
        'carbon_wp_comments.comment_author_url':'comment_author_url',
        'carbon_wp_comments.comment_author_IP':'comment_author_IP',
        'carbon_wp_comments.comment_date':'comment_date',
        'carbon_wp_comments.comment_date_gmt':'comment_date_gmt',
        'carbon_wp_comments.comment_content':'comment_content',
        'carbon_wp_comments.comment_karma':'comment_karma',
        'carbon_wp_comments.comment_approved':'comment_approved',
        'carbon_wp_comments.comment_agent':'comment_agent',
        'carbon_wp_comments.comment_type':'comment_type',
        'carbon_wp_comments.comment_parent':'comment_parent',
        'carbon_wp_comments.user_id':'user_id',
    },
    TYPE_VALIDATION: {
        'carbon_wp_comments.comment_ID': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_post_ID': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_author': { 
            MYSQL_TYPE: 'tinytext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_comments.comment_author_email': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_author_url': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_author_IP': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_date_gmt': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_content': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_comments.comment_karma': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_approved': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_agent': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_type': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.comment_parent': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_comments.user_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_links: {
    TABLE_NAME:'wp_links',
    LINK_ID: 'carbon_wp_links.link_id',
    LINK_URL: 'carbon_wp_links.link_url',
    LINK_NAME: 'carbon_wp_links.link_name',
    LINK_IMAGE: 'carbon_wp_links.link_image',
    LINK_TARGET: 'carbon_wp_links.link_target',
    LINK_DESCRIPTION: 'carbon_wp_links.link_description',
    LINK_VISIBLE: 'carbon_wp_links.link_visible',
    LINK_OWNER: 'carbon_wp_links.link_owner',
    LINK_RATING: 'carbon_wp_links.link_rating',
    LINK_UPDATED: 'carbon_wp_links.link_updated',
    LINK_REL: 'carbon_wp_links.link_rel',
    LINK_NOTES: 'carbon_wp_links.link_notes',
    LINK_RSS: 'carbon_wp_links.link_rss',
    PRIMARY: [
        'carbon_wp_links.link_id',
    ],
    PRIMARY_SHORT: [
        'link_id',
    ],
    COLUMNS: {
        'carbon_wp_links.link_id':'link_id',
        'carbon_wp_links.link_url':'link_url',
        'carbon_wp_links.link_name':'link_name',
        'carbon_wp_links.link_image':'link_image',
        'carbon_wp_links.link_target':'link_target',
        'carbon_wp_links.link_description':'link_description',
        'carbon_wp_links.link_visible':'link_visible',
        'carbon_wp_links.link_owner':'link_owner',
        'carbon_wp_links.link_rating':'link_rating',
        'carbon_wp_links.link_updated':'link_updated',
        'carbon_wp_links.link_rel':'link_rel',
        'carbon_wp_links.link_notes':'link_notes',
        'carbon_wp_links.link_rss':'link_rss',
    },
    TYPE_VALIDATION: {
        'carbon_wp_links.link_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_url': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_image': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_target': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '25', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_description': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_visible': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_owner': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_rating': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_updated': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_rel': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_links.link_notes': { 
            MYSQL_TYPE: 'mediumtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_links.link_rss': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_options: {
    TABLE_NAME:'wp_options',
    OPTION_ID: 'carbon_wp_options.option_id',
    OPTION_NAME: 'carbon_wp_options.option_name',
    OPTION_VALUE: 'carbon_wp_options.option_value',
    AUTOLOAD: 'carbon_wp_options.autoload',
    PRIMARY: [
        'carbon_wp_options.option_id',
    ],
    PRIMARY_SHORT: [
        'option_id',
    ],
    COLUMNS: {
        'carbon_wp_options.option_id':'option_id',
        'carbon_wp_options.option_name':'option_name',
        'carbon_wp_options.option_value':'option_value',
        'carbon_wp_options.autoload':'autoload',
    },
    TYPE_VALIDATION: {
        'carbon_wp_options.option_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_options.option_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '191', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_options.option_value': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_options.autoload': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_postmeta: {
    TABLE_NAME:'wp_postmeta',
    META_ID: 'carbon_wp_postmeta.meta_id',
    POST_ID: 'carbon_wp_postmeta.post_id',
    META_KEY: 'carbon_wp_postmeta.meta_key',
    META_VALUE: 'carbon_wp_postmeta.meta_value',
    PRIMARY: [
        'carbon_wp_postmeta.meta_id',
    ],
    PRIMARY_SHORT: [
        'meta_id',
    ],
    COLUMNS: {
        'carbon_wp_postmeta.meta_id':'meta_id',
        'carbon_wp_postmeta.post_id':'post_id',
        'carbon_wp_postmeta.meta_key':'meta_key',
        'carbon_wp_postmeta.meta_value':'meta_value',
    },
    TYPE_VALIDATION: {
        'carbon_wp_postmeta.meta_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_postmeta.post_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_postmeta.meta_key': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_postmeta.meta_value': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_posts: {
    TABLE_NAME:'wp_posts',
    ID: 'carbon_wp_posts.ID',
    POST_AUTHOR: 'carbon_wp_posts.post_author',
    POST_DATE: 'carbon_wp_posts.post_date',
    POST_DATE_GMT: 'carbon_wp_posts.post_date_gmt',
    POST_CONTENT: 'carbon_wp_posts.post_content',
    POST_TITLE: 'carbon_wp_posts.post_title',
    POST_EXCERPT: 'carbon_wp_posts.post_excerpt',
    POST_STATUS: 'carbon_wp_posts.post_status',
    COMMENT_STATUS: 'carbon_wp_posts.comment_status',
    PING_STATUS: 'carbon_wp_posts.ping_status',
    POST_PASSWORD: 'carbon_wp_posts.post_password',
    POST_NAME: 'carbon_wp_posts.post_name',
    TO_PING: 'carbon_wp_posts.to_ping',
    PINGED: 'carbon_wp_posts.pinged',
    POST_MODIFIED: 'carbon_wp_posts.post_modified',
    POST_MODIFIED_GMT: 'carbon_wp_posts.post_modified_gmt',
    POST_CONTENT_FILTERED: 'carbon_wp_posts.post_content_filtered',
    POST_PARENT: 'carbon_wp_posts.post_parent',
    GUID: 'carbon_wp_posts.guid',
    MENU_ORDER: 'carbon_wp_posts.menu_order',
    POST_TYPE: 'carbon_wp_posts.post_type',
    POST_MIME_TYPE: 'carbon_wp_posts.post_mime_type',
    COMMENT_COUNT: 'carbon_wp_posts.comment_count',
    PRIMARY: [
        'carbon_wp_posts.ID',
    ],
    PRIMARY_SHORT: [
        'ID',
    ],
    COLUMNS: {
        'carbon_wp_posts.ID':'ID',
        'carbon_wp_posts.post_author':'post_author',
        'carbon_wp_posts.post_date':'post_date',
        'carbon_wp_posts.post_date_gmt':'post_date_gmt',
        'carbon_wp_posts.post_content':'post_content',
        'carbon_wp_posts.post_title':'post_title',
        'carbon_wp_posts.post_excerpt':'post_excerpt',
        'carbon_wp_posts.post_status':'post_status',
        'carbon_wp_posts.comment_status':'comment_status',
        'carbon_wp_posts.ping_status':'ping_status',
        'carbon_wp_posts.post_password':'post_password',
        'carbon_wp_posts.post_name':'post_name',
        'carbon_wp_posts.to_ping':'to_ping',
        'carbon_wp_posts.pinged':'pinged',
        'carbon_wp_posts.post_modified':'post_modified',
        'carbon_wp_posts.post_modified_gmt':'post_modified_gmt',
        'carbon_wp_posts.post_content_filtered':'post_content_filtered',
        'carbon_wp_posts.post_parent':'post_parent',
        'carbon_wp_posts.guid':'guid',
        'carbon_wp_posts.menu_order':'menu_order',
        'carbon_wp_posts.post_type':'post_type',
        'carbon_wp_posts.post_mime_type':'post_mime_type',
        'carbon_wp_posts.comment_count':'comment_count',
    },
    TYPE_VALIDATION: {
        'carbon_wp_posts.ID': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_author': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_date': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_date_gmt': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_content': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.post_title': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.post_excerpt': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.post_status': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.comment_status': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.ping_status': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_password': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.to_ping': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.pinged': { 
            MYSQL_TYPE: 'text', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.post_modified': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_modified_gmt': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_content_filtered': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_posts.post_parent': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.guid': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.menu_order': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_type': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '20', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.post_mime_type': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_posts.comment_count': { 
            MYSQL_TYPE: 'bigint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_term_relationships: {
    TABLE_NAME:'wp_term_relationships',
    OBJECT_ID: 'carbon_wp_term_relationships.object_id',
    TERM_TAXONOMY_ID: 'carbon_wp_term_relationships.term_taxonomy_id',
    TERM_ORDER: 'carbon_wp_term_relationships.term_order',
    PRIMARY: [
        'carbon_wp_term_relationships.object_id',
        'carbon_wp_term_relationships.term_taxonomy_id',
    ],
    PRIMARY_SHORT: [
        'object_id','term_taxonomy_id',
    ],
    COLUMNS: {
        'carbon_wp_term_relationships.object_id':'object_id',
        'carbon_wp_term_relationships.term_taxonomy_id':'term_taxonomy_id',
        'carbon_wp_term_relationships.term_order':'term_order',
    },
    TYPE_VALIDATION: {
        'carbon_wp_term_relationships.object_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_relationships.term_taxonomy_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_relationships.term_order': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_term_taxonomy: {
    TABLE_NAME:'wp_term_taxonomy',
    TERM_TAXONOMY_ID: 'carbon_wp_term_taxonomy.term_taxonomy_id',
    TERM_ID: 'carbon_wp_term_taxonomy.term_id',
    TAXONOMY: 'carbon_wp_term_taxonomy.taxonomy',
    DESCRIPTION: 'carbon_wp_term_taxonomy.description',
    PARENT: 'carbon_wp_term_taxonomy.parent',
    COUNT: 'carbon_wp_term_taxonomy.count',
    PRIMARY: [
        'carbon_wp_term_taxonomy.term_taxonomy_id',
    ],
    PRIMARY_SHORT: [
        'term_taxonomy_id',
    ],
    COLUMNS: {
        'carbon_wp_term_taxonomy.term_taxonomy_id':'term_taxonomy_id',
        'carbon_wp_term_taxonomy.term_id':'term_id',
        'carbon_wp_term_taxonomy.taxonomy':'taxonomy',
        'carbon_wp_term_taxonomy.description':'description',
        'carbon_wp_term_taxonomy.parent':'parent',
        'carbon_wp_term_taxonomy.count':'count',
    },
    TYPE_VALIDATION: {
        'carbon_wp_term_taxonomy.term_taxonomy_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_taxonomy.term_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_taxonomy.taxonomy': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '32', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_taxonomy.description': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
        'carbon_wp_term_taxonomy.parent': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_term_taxonomy.count': { 
            MYSQL_TYPE: 'bigint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_termmeta: {
    TABLE_NAME:'wp_termmeta',
    META_ID: 'carbon_wp_termmeta.meta_id',
    TERM_ID: 'carbon_wp_termmeta.term_id',
    META_KEY: 'carbon_wp_termmeta.meta_key',
    META_VALUE: 'carbon_wp_termmeta.meta_value',
    PRIMARY: [
        'carbon_wp_termmeta.meta_id',
    ],
    PRIMARY_SHORT: [
        'meta_id',
    ],
    COLUMNS: {
        'carbon_wp_termmeta.meta_id':'meta_id',
        'carbon_wp_termmeta.term_id':'term_id',
        'carbon_wp_termmeta.meta_key':'meta_key',
        'carbon_wp_termmeta.meta_value':'meta_value',
    },
    TYPE_VALIDATION: {
        'carbon_wp_termmeta.meta_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_termmeta.term_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_termmeta.meta_key': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_termmeta.meta_value': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_terms: {
    TABLE_NAME:'wp_terms',
    TERM_ID: 'carbon_wp_terms.term_id',
    NAME: 'carbon_wp_terms.name',
    SLUG: 'carbon_wp_terms.slug',
    TERM_GROUP: 'carbon_wp_terms.term_group',
    PRIMARY: [
        'carbon_wp_terms.term_id',
    ],
    PRIMARY_SHORT: [
        'term_id',
    ],
    COLUMNS: {
        'carbon_wp_terms.term_id':'term_id',
        'carbon_wp_terms.name':'name',
        'carbon_wp_terms.slug':'slug',
        'carbon_wp_terms.term_group':'term_group',
    },
    TYPE_VALIDATION: {
        'carbon_wp_terms.term_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_terms.name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_terms.slug': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '200', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_terms.term_group': { 
            MYSQL_TYPE: 'bigint', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_usermeta: {
    TABLE_NAME:'wp_usermeta',
    UMETA_ID: 'carbon_wp_usermeta.umeta_id',
    USER_ID: 'carbon_wp_usermeta.user_id',
    META_KEY: 'carbon_wp_usermeta.meta_key',
    META_VALUE: 'carbon_wp_usermeta.meta_value',
    PRIMARY: [
        'carbon_wp_usermeta.umeta_id',
    ],
    PRIMARY_SHORT: [
        'umeta_id',
    ],
    COLUMNS: {
        'carbon_wp_usermeta.umeta_id':'umeta_id',
        'carbon_wp_usermeta.user_id':'user_id',
        'carbon_wp_usermeta.meta_key':'meta_key',
        'carbon_wp_usermeta.meta_value':'meta_value',
    },
    TYPE_VALIDATION: {
        'carbon_wp_usermeta.umeta_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_usermeta.user_id': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_usermeta.meta_key': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_usermeta.meta_value': { 
            MYSQL_TYPE: 'longtext', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: false 
        },
    },
    REGEX_VALIDATION: {
    }

  },

    wp_users: {
    TABLE_NAME:'wp_users',
    ID: 'carbon_wp_users.ID',
    USER_LOGIN: 'carbon_wp_users.user_login',
    USER_PASS: 'carbon_wp_users.user_pass',
    USER_NICENAME: 'carbon_wp_users.user_nicename',
    USER_EMAIL: 'carbon_wp_users.user_email',
    USER_URL: 'carbon_wp_users.user_url',
    USER_REGISTERED: 'carbon_wp_users.user_registered',
    USER_ACTIVATION_KEY: 'carbon_wp_users.user_activation_key',
    USER_STATUS: 'carbon_wp_users.user_status',
    DISPLAY_NAME: 'carbon_wp_users.display_name',
    PRIMARY: [
        'carbon_wp_users.ID',
    ],
    PRIMARY_SHORT: [
        'ID',
    ],
    COLUMNS: {
        'carbon_wp_users.ID':'ID',
        'carbon_wp_users.user_login':'user_login',
        'carbon_wp_users.user_pass':'user_pass',
        'carbon_wp_users.user_nicename':'user_nicename',
        'carbon_wp_users.user_email':'user_email',
        'carbon_wp_users.user_url':'user_url',
        'carbon_wp_users.user_registered':'user_registered',
        'carbon_wp_users.user_activation_key':'user_activation_key',
        'carbon_wp_users.user_status':'user_status',
        'carbon_wp_users.display_name':'display_name',
    },
    TYPE_VALIDATION: {
        'carbon_wp_users.ID': { 
            MYSQL_TYPE: 'bigint unsigned', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: true, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_login': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '60', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_pass': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_nicename': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '50', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_email': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_url': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '100', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_registered': { 
            MYSQL_TYPE: 'datetime', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_activation_key': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '255', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.user_status': { 
            MYSQL_TYPE: 'int', 
            MAX_LENGTH: '', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
        'carbon_wp_users.display_name': { 
            MYSQL_TYPE: 'varchar', 
            MAX_LENGTH: '250', 
            AUTO_INCREMENT: false, 
            SKIP_COLUMN_IN_POST: true 
        },
    },
    REGEX_VALIDATION: {
    }

  },
    
};

const COLUMNS = {
      
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

'carbon_wp_commentmeta.meta_id':'meta_id',
'carbon_wp_commentmeta.comment_id':'comment_id',
'carbon_wp_commentmeta.meta_key':'meta_key',
'carbon_wp_commentmeta.meta_value':'meta_value',

'carbon_wp_comments.comment_ID':'comment_ID',
'carbon_wp_comments.comment_post_ID':'comment_post_ID',
'carbon_wp_comments.comment_author':'comment_author',
'carbon_wp_comments.comment_author_email':'comment_author_email',
'carbon_wp_comments.comment_author_url':'comment_author_url',
'carbon_wp_comments.comment_author_IP':'comment_author_IP',
'carbon_wp_comments.comment_date':'comment_date',
'carbon_wp_comments.comment_date_gmt':'comment_date_gmt',
'carbon_wp_comments.comment_content':'comment_content',
'carbon_wp_comments.comment_karma':'comment_karma',
'carbon_wp_comments.comment_approved':'comment_approved',
'carbon_wp_comments.comment_agent':'comment_agent',
'carbon_wp_comments.comment_type':'comment_type',
'carbon_wp_comments.comment_parent':'comment_parent',
'carbon_wp_comments.user_id':'user_id',

'carbon_wp_links.link_id':'link_id',
'carbon_wp_links.link_url':'link_url',
'carbon_wp_links.link_name':'link_name',
'carbon_wp_links.link_image':'link_image',
'carbon_wp_links.link_target':'link_target',
'carbon_wp_links.link_description':'link_description',
'carbon_wp_links.link_visible':'link_visible',
'carbon_wp_links.link_owner':'link_owner',
'carbon_wp_links.link_rating':'link_rating',
'carbon_wp_links.link_updated':'link_updated',
'carbon_wp_links.link_rel':'link_rel',
'carbon_wp_links.link_notes':'link_notes',
'carbon_wp_links.link_rss':'link_rss',

'carbon_wp_options.option_id':'option_id',
'carbon_wp_options.option_name':'option_name',
'carbon_wp_options.option_value':'option_value',
'carbon_wp_options.autoload':'autoload',

'carbon_wp_postmeta.meta_id':'meta_id',
'carbon_wp_postmeta.post_id':'post_id',
'carbon_wp_postmeta.meta_key':'meta_key',
'carbon_wp_postmeta.meta_value':'meta_value',

'carbon_wp_posts.ID':'ID',
'carbon_wp_posts.post_author':'post_author',
'carbon_wp_posts.post_date':'post_date',
'carbon_wp_posts.post_date_gmt':'post_date_gmt',
'carbon_wp_posts.post_content':'post_content',
'carbon_wp_posts.post_title':'post_title',
'carbon_wp_posts.post_excerpt':'post_excerpt',
'carbon_wp_posts.post_status':'post_status',
'carbon_wp_posts.comment_status':'comment_status',
'carbon_wp_posts.ping_status':'ping_status',
'carbon_wp_posts.post_password':'post_password',
'carbon_wp_posts.post_name':'post_name',
'carbon_wp_posts.to_ping':'to_ping',
'carbon_wp_posts.pinged':'pinged',
'carbon_wp_posts.post_modified':'post_modified',
'carbon_wp_posts.post_modified_gmt':'post_modified_gmt',
'carbon_wp_posts.post_content_filtered':'post_content_filtered',
'carbon_wp_posts.post_parent':'post_parent',
'carbon_wp_posts.guid':'guid',
'carbon_wp_posts.menu_order':'menu_order',
'carbon_wp_posts.post_type':'post_type',
'carbon_wp_posts.post_mime_type':'post_mime_type',
'carbon_wp_posts.comment_count':'comment_count',

'carbon_wp_term_relationships.object_id':'object_id',
'carbon_wp_term_relationships.term_taxonomy_id':'term_taxonomy_id',
'carbon_wp_term_relationships.term_order':'term_order',

'carbon_wp_term_taxonomy.term_taxonomy_id':'term_taxonomy_id',
'carbon_wp_term_taxonomy.term_id':'term_id',
'carbon_wp_term_taxonomy.taxonomy':'taxonomy',
'carbon_wp_term_taxonomy.description':'description',
'carbon_wp_term_taxonomy.parent':'parent',
'carbon_wp_term_taxonomy.count':'count',

'carbon_wp_termmeta.meta_id':'meta_id',
'carbon_wp_termmeta.term_id':'term_id',
'carbon_wp_termmeta.meta_key':'meta_key',
'carbon_wp_termmeta.meta_value':'meta_value',

'carbon_wp_terms.term_id':'term_id',
'carbon_wp_terms.name':'name',
'carbon_wp_terms.slug':'slug',
'carbon_wp_terms.term_group':'term_group',

'carbon_wp_usermeta.umeta_id':'umeta_id',
'carbon_wp_usermeta.user_id':'user_id',
'carbon_wp_usermeta.meta_key':'meta_key',
'carbon_wp_usermeta.meta_value':'meta_value',

'carbon_wp_users.ID':'ID',
'carbon_wp_users.user_login':'user_login',
'carbon_wp_users.user_pass':'user_pass',
'carbon_wp_users.user_nicename':'user_nicename',
'carbon_wp_users.user_email':'user_email',
'carbon_wp_users.user_url':'user_url',
'carbon_wp_users.user_registered':'user_registered',
'carbon_wp_users.user_activation_key':'user_activation_key',
'carbon_wp_users.user_status':'user_status',
'carbon_wp_users.display_name':'display_name',

};

export const convertForRequestBody = function (restfulObject, tableName, regexErrorHandler) {

    let payload = {};

    const tableNames = Array.isArray(tableName) ? tableName : [tableName];
    
    tableNames.forEach((table) => {

        Object.keys(restfulObject).forEach(value => {

            let shortReference = value.toUpperCase();
            
            switch (value) {
                case C6.GET:
                case C6.POST:
                case C6.UPDATE:
                case C6.REPLACE:
                case C6.DELETE:
                case C6.WHERE:
                case C6.JOIN:
                case C6.PAGINATION:
					if (Array.isArray(restfulObject[value])) {
						payload[value] = restfulObject[value].sort()
					} else if (typeof restfulObject[value] === 'object' && restfulObject[value] !== null) {
						payload[value] = Object.keys(restfulObject[value])
							.sort()
							.reduce(function (acc, key) {
								acc[key] = restfulObject[value][key];
								return acc;
							}, {})
					} 
                    return
                default:
            }

            if (shortReference in C6[table]) {

                const longName = C6[table][shortReference];

                payload[longName] = restfulObject[value]

                const regexValidations = C6[table].REGEX_VALIDATION[longName]

                if (regexValidations instanceof RegExp) {

                    if (false === regexValidations.test(restfulObject[value])) {

                        regexErrorHandler('Failed to match regex (' + regexValidations + ') for column (' + longName + ')')

                        throw Error('Failed to match regex (' + regexValidations + ') for column (' + longName + ')')

                    }

                } else if (typeof regexValidations === 'object' && regexValidations !== null) {

                    Object.keys(regexValidations)?.map((errorMessage) => {

                        const regex = regexValidations[errorMessage];
                        
                        if (false === regex.test(restfulObject[value])) {

                            const devErrorMessage = 'Failed to match regex (' + regex + ') for column (' + longName + ')';
                            
                            regexErrorHandler(errorMessage ?? devErrorMessage)
                            
                            throw Error(devErrorMessage)

                        }
                        
                    })
                    
                }
                
            }
            
        })

        return true;

    });

	return Object.keys(payload)
		.sort()
		.reduce(function (acc, key) {
			acc[key] = payload[key];
			return acc;
		}, {})

};


