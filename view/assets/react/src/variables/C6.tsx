

export const C6 = {

    SELECT: 'select',
    UPDATE: 'update',
    WHERE: 'where',
    LIMIT: 'limit',
    PAGINATION: 'pagination',
    ORDER: 'order',
    DESC: ' DESC',
    ASC: ' ASC',
    JOIN: 'join',
    INNER: 'inner',
    LEFT: 'left',
    RIGHT: 'right',
    DISTINCT: 'distinct',
    COUNT: 'count',
    SUM: 'sum',
    MIN: 'min',
    MAX: 'max',
    GROUP_CONCAT: 'GROUP_CONCAT',
    
    

  carbon_comments: {
    TABLE_NAME:'carbon_comments',
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
    REGEX_VALIDATION: {
    }

  },

  carbons: {
    TABLE_NAME:'carbons',
    ENTITY_PK: 'carbons.entity_pk',
    ENTITY_FK: 'carbons.entity_fk',
    ENTITY_TAG: 'carbons.entity_tag',
    PRIMARY: [
        'carbons.entity_pk',

    ],
    COLUMNS: {
      'carbons.entity_pk':'entity_pk',
'carbons.entity_fk':'entity_fk',
'carbons.entity_tag':'entity_tag',
    },
    REGEX_VALIDATION: {
    }

  },

  carbon_feature_group_references: {
    TABLE_NAME:'carbon_feature_group_references',
    FEATURE_ENTITY_ID: 'carbon_feature_group_references.feature_entity_id',
    GROUP_ENTITY_ID: 'carbon_feature_group_references.group_entity_id',
    PRIMARY: [
            ],
    COLUMNS: {
      'carbon_feature_group_references.feature_entity_id':'feature_entity_id',
'carbon_feature_group_references.group_entity_id':'group_entity_id',
    },
    REGEX_VALIDATION: {
    }

  },

  carbon_features: {
    TABLE_NAME:'carbon_features',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_group_references: {
    TABLE_NAME:'carbon_group_references',
    GROUP_ID: 'carbon_group_references.group_id',
    ALLOWED_TO_GRANT_GROUP_ID: 'carbon_group_references.allowed_to_grant_group_id',
    PRIMARY: [
            ],
    COLUMNS: {
      'carbon_group_references.group_id':'group_id',
'carbon_group_references.allowed_to_grant_group_id':'allowed_to_grant_group_id',
    },
    REGEX_VALIDATION: {
    }

  },

  carbon_groups: {
    TABLE_NAME:'carbon_groups',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_location_references: {
    TABLE_NAME:'carbon_location_references',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_locations: {
    TABLE_NAME:'carbon_locations',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_photos: {
    TABLE_NAME:'carbon_photos',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_reports: {
    TABLE_NAME:'carbon_reports',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_user_followers: {
    TABLE_NAME:'carbon_user_followers',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_user_groups: {
    TABLE_NAME:'carbon_user_groups',
    GROUP_ID: 'carbon_user_groups.group_id',
    USER_ID: 'carbon_user_groups.user_id',
    PRIMARY: [
            ],
    COLUMNS: {
      'carbon_user_groups.group_id':'group_id',
'carbon_user_groups.user_id':'user_id',
    },
    REGEX_VALIDATION: {
    }

  },

  carbon_user_messages: {
    TABLE_NAME:'carbon_user_messages',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_user_sessions: {
    TABLE_NAME:'carbon_user_sessions',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_user_tasks: {
    TABLE_NAME:'carbon_user_tasks',
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
    REGEX_VALIDATION: {
    }

  },

  carbon_users: {
    TABLE_NAME:'carbon_users',
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
    REGEX_VALIDATION: {
        'carbon_users.user_id': /^([a-fA-F0-9]{20,35})$/,
        'carbon_users.user_username': /^[A-Za-z0-9_-]{4,16}/,
    }

  },

  history_logs: {
    TABLE_NAME:'history_logs',
    UUID: 'history_logs.uuid',
    RESOURCE_TYPE: 'history_logs.resource_type',
    RESOURCE_UUID: 'history_logs.resource_uuid',
    OPERATION_TYPE: 'history_logs.operation_type',
    DATA: 'history_logs.data',
    PRIMARY: [
            ],
    COLUMNS: {
      'history_logs.uuid':'uuid',
'history_logs.resource_type':'resource_type',
'history_logs.resource_uuid':'resource_uuid',
'history_logs.operation_type':'operation_type',
'history_logs.data':'data',
    },
    REGEX_VALIDATION: {
    }

  },

  sessions: {
    TABLE_NAME:'sessions',
    USER_ID: 'sessions.user_id',
    USER_IP: 'sessions.user_ip',
    SESSION_ID: 'sessions.session_id',
    SESSION_EXPIRES: 'sessions.session_expires',
    SESSION_DATA: 'sessions.session_data',
    USER_ONLINE_STATUS: 'sessions.user_online_status',
    PRIMARY: [
        'sessions.session_id',

    ],
    COLUMNS: {
      'sessions.user_id':'user_id',
'sessions.user_ip':'user_ip',
'sessions.session_id':'session_id',
'sessions.session_expires':'session_expires',
'sessions.session_data':'session_data',
'sessions.user_online_status':'user_online_status',
    },
    REGEX_VALIDATION: {
    }

  },
    
};



export interface  iCarbon_Comments{
      'parent_id'?: string;
'comment_id'?: string;
'user_id'?: string;
'comment'?: string;
}
  

export interface  iCarbons{
      'entity_pk'?: string;
'entity_fk'?: string;
'entity_tag'?: string;
}
  

export interface  iCarbon_Feature_Group_References{
      'feature_entity_id'?: string;
'group_entity_id'?: string;
}
  

export interface  iCarbon_Features{
      'feature_entity_id'?: string;
'feature_code'?: string;
'feature_creation_date'?: string;
}
  

export interface  iCarbon_Group_References{
      'group_id'?: string;
'allowed_to_grant_group_id'?: string;
}
  

export interface  iCarbon_Groups{
      'group_name'?: string;
'entity_id'?: string;
'created_by'?: string;
'creation_date'?: string;
}
  

export interface  iCarbon_Location_References{
      'entity_reference'?: string;
'location_reference'?: string;
'location_time'?: string;
}
  

export interface  iCarbon_Locations{
      'entity_id'?: string;
'latitude'?: string;
'longitude'?: string;
'street'?: string;
'city'?: string;
'state'?: string;
'elevation'?: string;
'zip'?: string;
}
  

export interface  iCarbon_Photos{
      'parent_id'?: string;
'photo_id'?: string;
'user_id'?: string;
'photo_path'?: string;
'photo_description'?: string;
}
  

export interface  iCarbon_Reports{
      'log_level'?: string;
'report'?: string;
'date'?: string;
'call_trace'?: string;
}
  

export interface  iCarbon_User_Followers{
      'follower_table_id'?: string;
'follows_user_id'?: string;
'user_id'?: string;
}
  

export interface  iCarbon_User_Groups{
      'group_id'?: string;
'user_id'?: string;
}
  

export interface  iCarbon_User_Messages{
      'message_id'?: string;
'from_user_id'?: string;
'to_user_id'?: string;
'message'?: string;
'message_read'?: string;
'creation_date'?: string;
}
  

export interface  iCarbon_User_Sessions{
      'user_id'?: string;
'user_ip'?: string;
'session_id'?: string;
'session_expires'?: string;
'session_data'?: string;
'user_online_status'?: string;
}
  

export interface  iCarbon_User_Tasks{
      'task_id'?: string;
'user_id'?: string;
'from_id'?: string;
'task_name'?: string;
'task_description'?: string;
'percent_complete'?: string;
'start_date'?: string;
'end_date'?: string;
}
  

export interface  iCarbon_Users{
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
'user_rank'?: string;
'user_email'?: string;
'user_email_code'?: string;
'user_email_confirmed'?: string;
'user_generated_string'?: string;
'user_membership'?: string;
'user_deactivated'?: string;
'user_last_login'?: string;
'user_ip'?: string;
'user_education_history'?: string;
'user_location'?: string;
'user_creation_date'?: string;
}
  

export interface  iHistory_Logs{
      'uuid'?: string;
'resource_type'?: string;
'resource_uuid'?: string;
'operation_type'?: string;
'data'?: string;
}
  

export interface  iSessions{
      'user_id'?: string;
'user_ip'?: string;
'session_id'?: string;
'session_expires'?: string;
'session_data'?: string;
'user_online_status'?: string;
}
  

export const COLUMNS = {
      
'carbon_comments.parent_id':'parent_id',
'carbon_comments.comment_id':'comment_id',
'carbon_comments.user_id':'user_id',
'carbon_comments.comment':'comment',

'carbons.entity_pk':'entity_pk',
'carbons.entity_fk':'entity_fk',
'carbons.entity_tag':'entity_tag',

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

'history_logs.uuid':'uuid',
'history_logs.resource_type':'resource_type',
'history_logs.resource_uuid':'resource_uuid',
'history_logs.operation_type':'operation_type',
'history_logs.data':'data',

'sessions.user_id':'user_id',
'sessions.user_ip':'user_ip',
'sessions.session_id':'session_id',
'sessions.session_expires':'session_expires',
'sessions.session_data':'session_data',
'sessions.user_online_status':'user_online_status',

};

//export type RestTables = "$all_table_names_types";

export type RestTableInterfaces = iCarbon_Comments | iCarbons | iCarbon_Feature_Group_References | iCarbon_Features | iCarbon_Group_References | iCarbon_Groups | iCarbon_Location_References | iCarbon_Locations | iCarbon_Photos | iCarbon_Reports | iCarbon_User_Followers | iCarbon_User_Groups | iCarbon_User_Messages | iCarbon_User_Sessions | iCarbon_User_Tasks | iCarbon_Users | iHistory_Logs | iSessions;

export const convertForRequestBody = function(restfulObject: RestTableInterfaces, tableName: string) {
  let payload = {};
  Object.keys(restfulObject).map(value => {
    let exactReference = value.toUpperCase();
    if (exactReference in C6[tableName]) {
      payload[C6[tableName][exactReference]] = restfulObject[value]
    }
    return true;
  });
  return payload;
};

