

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

  creation_logs: {
    TABLE_NAME:'creation_logs',
    UUID: 'creation_logs.uuid',
    RESOURCE_TYPE: 'creation_logs.resource_type',
    RESOURCE_UUID: 'creation_logs.resource_uuid',
    PRIMARY: [
            ],
    COLUMNS: {
      'creation_logs.uuid':'uuid',
'creation_logs.resource_type':'resource_type',
'creation_logs.resource_uuid':'resource_uuid',
    },
    REGEX_VALIDATION: {
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

  wp_commentmeta: {
    TABLE_NAME:'wp_commentmeta',
    META_ID: 'wp_commentmeta.meta_id',
    COMMENT_ID: 'wp_commentmeta.comment_id',
    META_KEY: 'wp_commentmeta.meta_key',
    META_VALUE: 'wp_commentmeta.meta_value',
    PRIMARY: [
        'wp_commentmeta.meta_id',
    ],
    COLUMNS: {
      'wp_commentmeta.meta_id':'meta_id',
'wp_commentmeta.comment_id':'comment_id',
'wp_commentmeta.meta_key':'meta_key',
'wp_commentmeta.meta_value':'meta_value',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_comments: {
    TABLE_NAME:'wp_comments',
    COMMENT_ID: 'wp_comments.comment_ID',
    COMMENT_POST_ID: 'wp_comments.comment_post_ID',
    COMMENT_AUTHOR: 'wp_comments.comment_author',
    COMMENT_AUTHOR_EMAIL: 'wp_comments.comment_author_email',
    COMMENT_AUTHOR_URL: 'wp_comments.comment_author_url',
    COMMENT_AUTHOR_IP: 'wp_comments.comment_author_IP',
    COMMENT_DATE: 'wp_comments.comment_date',
    COMMENT_DATE_GMT: 'wp_comments.comment_date_gmt',
    COMMENT_CONTENT: 'wp_comments.comment_content',
    COMMENT_KARMA: 'wp_comments.comment_karma',
    COMMENT_APPROVED: 'wp_comments.comment_approved',
    COMMENT_AGENT: 'wp_comments.comment_agent',
    COMMENT_TYPE: 'wp_comments.comment_type',
    COMMENT_PARENT: 'wp_comments.comment_parent',
    USER_ID: 'wp_comments.user_id',
    PRIMARY: [
        'wp_comments.comment_ID',
    ],
    COLUMNS: {
      'wp_comments.comment_ID':'comment_ID',
'wp_comments.comment_post_ID':'comment_post_ID',
'wp_comments.comment_author':'comment_author',
'wp_comments.comment_author_email':'comment_author_email',
'wp_comments.comment_author_url':'comment_author_url',
'wp_comments.comment_author_IP':'comment_author_IP',
'wp_comments.comment_date':'comment_date',
'wp_comments.comment_date_gmt':'comment_date_gmt',
'wp_comments.comment_content':'comment_content',
'wp_comments.comment_karma':'comment_karma',
'wp_comments.comment_approved':'comment_approved',
'wp_comments.comment_agent':'comment_agent',
'wp_comments.comment_type':'comment_type',
'wp_comments.comment_parent':'comment_parent',
'wp_comments.user_id':'user_id',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_links: {
    TABLE_NAME:'wp_links',
    LINK_ID: 'wp_links.link_id',
    LINK_URL: 'wp_links.link_url',
    LINK_NAME: 'wp_links.link_name',
    LINK_IMAGE: 'wp_links.link_image',
    LINK_TARGET: 'wp_links.link_target',
    LINK_DESCRIPTION: 'wp_links.link_description',
    LINK_VISIBLE: 'wp_links.link_visible',
    LINK_OWNER: 'wp_links.link_owner',
    LINK_RATING: 'wp_links.link_rating',
    LINK_UPDATED: 'wp_links.link_updated',
    LINK_REL: 'wp_links.link_rel',
    LINK_NOTES: 'wp_links.link_notes',
    LINK_RSS: 'wp_links.link_rss',
    PRIMARY: [
        'wp_links.link_id',
    ],
    COLUMNS: {
      'wp_links.link_id':'link_id',
'wp_links.link_url':'link_url',
'wp_links.link_name':'link_name',
'wp_links.link_image':'link_image',
'wp_links.link_target':'link_target',
'wp_links.link_description':'link_description',
'wp_links.link_visible':'link_visible',
'wp_links.link_owner':'link_owner',
'wp_links.link_rating':'link_rating',
'wp_links.link_updated':'link_updated',
'wp_links.link_rel':'link_rel',
'wp_links.link_notes':'link_notes',
'wp_links.link_rss':'link_rss',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_options: {
    TABLE_NAME:'wp_options',
    OPTION_ID: 'wp_options.option_id',
    OPTION_NAME: 'wp_options.option_name',
    OPTION_VALUE: 'wp_options.option_value',
    AUTOLOAD: 'wp_options.autoload',
    PRIMARY: [
        'wp_options.option_id',
    ],
    COLUMNS: {
      'wp_options.option_id':'option_id',
'wp_options.option_name':'option_name',
'wp_options.option_value':'option_value',
'wp_options.autoload':'autoload',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_postmeta: {
    TABLE_NAME:'wp_postmeta',
    META_ID: 'wp_postmeta.meta_id',
    POST_ID: 'wp_postmeta.post_id',
    META_KEY: 'wp_postmeta.meta_key',
    META_VALUE: 'wp_postmeta.meta_value',
    PRIMARY: [
        'wp_postmeta.meta_id',
    ],
    COLUMNS: {
      'wp_postmeta.meta_id':'meta_id',
'wp_postmeta.post_id':'post_id',
'wp_postmeta.meta_key':'meta_key',
'wp_postmeta.meta_value':'meta_value',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_posts: {
    TABLE_NAME:'wp_posts',
    ID: 'wp_posts.ID',
    POST_AUTHOR: 'wp_posts.post_author',
    POST_DATE: 'wp_posts.post_date',
    POST_DATE_GMT: 'wp_posts.post_date_gmt',
    POST_CONTENT: 'wp_posts.post_content',
    POST_TITLE: 'wp_posts.post_title',
    POST_EXCERPT: 'wp_posts.post_excerpt',
    POST_STATUS: 'wp_posts.post_status',
    COMMENT_STATUS: 'wp_posts.comment_status',
    PING_STATUS: 'wp_posts.ping_status',
    POST_PASSWORD: 'wp_posts.post_password',
    POST_NAME: 'wp_posts.post_name',
    TO_PING: 'wp_posts.to_ping',
    PINGED: 'wp_posts.pinged',
    POST_MODIFIED: 'wp_posts.post_modified',
    POST_MODIFIED_GMT: 'wp_posts.post_modified_gmt',
    POST_CONTENT_FILTERED: 'wp_posts.post_content_filtered',
    POST_PARENT: 'wp_posts.post_parent',
    GUID: 'wp_posts.guid',
    MENU_ORDER: 'wp_posts.menu_order',
    POST_TYPE: 'wp_posts.post_type',
    POST_MIME_TYPE: 'wp_posts.post_mime_type',
    COMMENT_COUNT: 'wp_posts.comment_count',
    PRIMARY: [
        'wp_posts.ID',
    ],
    COLUMNS: {
      'wp_posts.ID':'ID',
'wp_posts.post_author':'post_author',
'wp_posts.post_date':'post_date',
'wp_posts.post_date_gmt':'post_date_gmt',
'wp_posts.post_content':'post_content',
'wp_posts.post_title':'post_title',
'wp_posts.post_excerpt':'post_excerpt',
'wp_posts.post_status':'post_status',
'wp_posts.comment_status':'comment_status',
'wp_posts.ping_status':'ping_status',
'wp_posts.post_password':'post_password',
'wp_posts.post_name':'post_name',
'wp_posts.to_ping':'to_ping',
'wp_posts.pinged':'pinged',
'wp_posts.post_modified':'post_modified',
'wp_posts.post_modified_gmt':'post_modified_gmt',
'wp_posts.post_content_filtered':'post_content_filtered',
'wp_posts.post_parent':'post_parent',
'wp_posts.guid':'guid',
'wp_posts.menu_order':'menu_order',
'wp_posts.post_type':'post_type',
'wp_posts.post_mime_type':'post_mime_type',
'wp_posts.comment_count':'comment_count',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_term_relationships: {
    TABLE_NAME:'wp_term_relationships',
    OBJECT_ID: 'wp_term_relationships.object_id',
    TERM_TAXONOMY_ID: 'wp_term_relationships.term_taxonomy_id',
    TERM_ORDER: 'wp_term_relationships.term_order',
    PRIMARY: [
        'wp_term_relationships.object_id',
'wp_term_relationships.term_taxonomy_id',
    ],
    COLUMNS: {
      'wp_term_relationships.object_id':'object_id',
'wp_term_relationships.term_taxonomy_id':'term_taxonomy_id',
'wp_term_relationships.term_order':'term_order',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_term_taxonomy: {
    TABLE_NAME:'wp_term_taxonomy',
    TERM_TAXONOMY_ID: 'wp_term_taxonomy.term_taxonomy_id',
    TERM_ID: 'wp_term_taxonomy.term_id',
    TAXONOMY: 'wp_term_taxonomy.taxonomy',
    DESCRIPTION: 'wp_term_taxonomy.description',
    PARENT: 'wp_term_taxonomy.parent',
    COUNT: 'wp_term_taxonomy.count',
    PRIMARY: [
        'wp_term_taxonomy.term_taxonomy_id',
    ],
    COLUMNS: {
      'wp_term_taxonomy.term_taxonomy_id':'term_taxonomy_id',
'wp_term_taxonomy.term_id':'term_id',
'wp_term_taxonomy.taxonomy':'taxonomy',
'wp_term_taxonomy.description':'description',
'wp_term_taxonomy.parent':'parent',
'wp_term_taxonomy.count':'count',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_termmeta: {
    TABLE_NAME:'wp_termmeta',
    META_ID: 'wp_termmeta.meta_id',
    TERM_ID: 'wp_termmeta.term_id',
    META_KEY: 'wp_termmeta.meta_key',
    META_VALUE: 'wp_termmeta.meta_value',
    PRIMARY: [
        'wp_termmeta.meta_id',
    ],
    COLUMNS: {
      'wp_termmeta.meta_id':'meta_id',
'wp_termmeta.term_id':'term_id',
'wp_termmeta.meta_key':'meta_key',
'wp_termmeta.meta_value':'meta_value',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_terms: {
    TABLE_NAME:'wp_terms',
    TERM_ID: 'wp_terms.term_id',
    NAME: 'wp_terms.name',
    SLUG: 'wp_terms.slug',
    TERM_GROUP: 'wp_terms.term_group',
    PRIMARY: [
        'wp_terms.term_id',
    ],
    COLUMNS: {
      'wp_terms.term_id':'term_id',
'wp_terms.name':'name',
'wp_terms.slug':'slug',
'wp_terms.term_group':'term_group',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_usermeta: {
    TABLE_NAME:'wp_usermeta',
    UMETA_ID: 'wp_usermeta.umeta_id',
    USER_ID: 'wp_usermeta.user_id',
    META_KEY: 'wp_usermeta.meta_key',
    META_VALUE: 'wp_usermeta.meta_value',
    PRIMARY: [
        'wp_usermeta.umeta_id',
    ],
    COLUMNS: {
      'wp_usermeta.umeta_id':'umeta_id',
'wp_usermeta.user_id':'user_id',
'wp_usermeta.meta_key':'meta_key',
'wp_usermeta.meta_value':'meta_value',
    },
    REGEX_VALIDATION: {
    }

  },

  wp_users: {
    TABLE_NAME:'wp_users',
    ID: 'wp_users.ID',
    USER_LOGIN: 'wp_users.user_login',
    USER_PASS: 'wp_users.user_pass',
    USER_NICENAME: 'wp_users.user_nicename',
    USER_EMAIL: 'wp_users.user_email',
    USER_URL: 'wp_users.user_url',
    USER_REGISTERED: 'wp_users.user_registered',
    USER_ACTIVATION_KEY: 'wp_users.user_activation_key',
    USER_STATUS: 'wp_users.user_status',
    DISPLAY_NAME: 'wp_users.display_name',
    PRIMARY: [
        'wp_users.ID',
    ],
    COLUMNS: {
      'wp_users.ID':'ID',
'wp_users.user_login':'user_login',
'wp_users.user_pass':'user_pass',
'wp_users.user_nicename':'user_nicename',
'wp_users.user_email':'user_email',
'wp_users.user_url':'user_url',
'wp_users.user_registered':'user_registered',
'wp_users.user_activation_key':'user_activation_key',
'wp_users.user_status':'user_status',
'wp_users.display_name':'display_name',
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
  

export interface  iCreation_Logs{
      'uuid'?: string;
'resource_type'?: string;
'resource_uuid'?: string;
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
  

export interface  iWp_Commentmeta{
      'meta_id'?: string;
'comment_id'?: string;
'meta_key'?: string;
'meta_value'?: string;
}
  

export interface  iWp_Comments{
      'comment_ID'?: string;
'comment_post_ID'?: string;
'comment_author'?: string;
'comment_author_email'?: string;
'comment_author_url'?: string;
'comment_author_IP'?: string;
'comment_date'?: string;
'comment_date_gmt'?: string;
'comment_content'?: string;
'comment_karma'?: string;
'comment_approved'?: string;
'comment_agent'?: string;
'comment_type'?: string;
'comment_parent'?: string;
'user_id'?: string;
}
  

export interface  iWp_Links{
      'link_id'?: string;
'link_url'?: string;
'link_name'?: string;
'link_image'?: string;
'link_target'?: string;
'link_description'?: string;
'link_visible'?: string;
'link_owner'?: string;
'link_rating'?: string;
'link_updated'?: string;
'link_rel'?: string;
'link_notes'?: string;
'link_rss'?: string;
}
  

export interface  iWp_Options{
      'option_id'?: string;
'option_name'?: string;
'option_value'?: string;
'autoload'?: string;
}
  

export interface  iWp_Postmeta{
      'meta_id'?: string;
'post_id'?: string;
'meta_key'?: string;
'meta_value'?: string;
}
  

export interface  iWp_Posts{
      'ID'?: string;
'post_author'?: string;
'post_date'?: string;
'post_date_gmt'?: string;
'post_content'?: string;
'post_title'?: string;
'post_excerpt'?: string;
'post_status'?: string;
'comment_status'?: string;
'ping_status'?: string;
'post_password'?: string;
'post_name'?: string;
'to_ping'?: string;
'pinged'?: string;
'post_modified'?: string;
'post_modified_gmt'?: string;
'post_content_filtered'?: string;
'post_parent'?: string;
'guid'?: string;
'menu_order'?: string;
'post_type'?: string;
'post_mime_type'?: string;
'comment_count'?: string;
}
  

export interface  iWp_Term_Relationships{
      'object_id'?: string;
'term_taxonomy_id'?: string;
'term_order'?: string;
}
  

export interface  iWp_Term_Taxonomy{
      'term_taxonomy_id'?: string;
'term_id'?: string;
'taxonomy'?: string;
'description'?: string;
'parent'?: string;
'count'?: string;
}
  

export interface  iWp_Termmeta{
      'meta_id'?: string;
'term_id'?: string;
'meta_key'?: string;
'meta_value'?: string;
}
  

export interface  iWp_Terms{
      'term_id'?: string;
'name'?: string;
'slug'?: string;
'term_group'?: string;
}
  

export interface  iWp_Usermeta{
      'umeta_id'?: string;
'user_id'?: string;
'meta_key'?: string;
'meta_value'?: string;
}
  

export interface  iWp_Users{
      'ID'?: string;
'user_login'?: string;
'user_pass'?: string;
'user_nicename'?: string;
'user_email'?: string;
'user_url'?: string;
'user_registered'?: string;
'user_activation_key'?: string;
'user_status'?: string;
'display_name'?: string;
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

'creation_logs.uuid':'uuid',
'creation_logs.resource_type':'resource_type',
'creation_logs.resource_uuid':'resource_uuid',

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

'wp_commentmeta.meta_id':'meta_id',
'wp_commentmeta.comment_id':'comment_id',
'wp_commentmeta.meta_key':'meta_key',
'wp_commentmeta.meta_value':'meta_value',

'wp_comments.comment_ID':'comment_ID',
'wp_comments.comment_post_ID':'comment_post_ID',
'wp_comments.comment_author':'comment_author',
'wp_comments.comment_author_email':'comment_author_email',
'wp_comments.comment_author_url':'comment_author_url',
'wp_comments.comment_author_IP':'comment_author_IP',
'wp_comments.comment_date':'comment_date',
'wp_comments.comment_date_gmt':'comment_date_gmt',
'wp_comments.comment_content':'comment_content',
'wp_comments.comment_karma':'comment_karma',
'wp_comments.comment_approved':'comment_approved',
'wp_comments.comment_agent':'comment_agent',
'wp_comments.comment_type':'comment_type',
'wp_comments.comment_parent':'comment_parent',
'wp_comments.user_id':'user_id',

'wp_links.link_id':'link_id',
'wp_links.link_url':'link_url',
'wp_links.link_name':'link_name',
'wp_links.link_image':'link_image',
'wp_links.link_target':'link_target',
'wp_links.link_description':'link_description',
'wp_links.link_visible':'link_visible',
'wp_links.link_owner':'link_owner',
'wp_links.link_rating':'link_rating',
'wp_links.link_updated':'link_updated',
'wp_links.link_rel':'link_rel',
'wp_links.link_notes':'link_notes',
'wp_links.link_rss':'link_rss',

'wp_options.option_id':'option_id',
'wp_options.option_name':'option_name',
'wp_options.option_value':'option_value',
'wp_options.autoload':'autoload',

'wp_postmeta.meta_id':'meta_id',
'wp_postmeta.post_id':'post_id',
'wp_postmeta.meta_key':'meta_key',
'wp_postmeta.meta_value':'meta_value',

'wp_posts.ID':'ID',
'wp_posts.post_author':'post_author',
'wp_posts.post_date':'post_date',
'wp_posts.post_date_gmt':'post_date_gmt',
'wp_posts.post_content':'post_content',
'wp_posts.post_title':'post_title',
'wp_posts.post_excerpt':'post_excerpt',
'wp_posts.post_status':'post_status',
'wp_posts.comment_status':'comment_status',
'wp_posts.ping_status':'ping_status',
'wp_posts.post_password':'post_password',
'wp_posts.post_name':'post_name',
'wp_posts.to_ping':'to_ping',
'wp_posts.pinged':'pinged',
'wp_posts.post_modified':'post_modified',
'wp_posts.post_modified_gmt':'post_modified_gmt',
'wp_posts.post_content_filtered':'post_content_filtered',
'wp_posts.post_parent':'post_parent',
'wp_posts.guid':'guid',
'wp_posts.menu_order':'menu_order',
'wp_posts.post_type':'post_type',
'wp_posts.post_mime_type':'post_mime_type',
'wp_posts.comment_count':'comment_count',

'wp_term_relationships.object_id':'object_id',
'wp_term_relationships.term_taxonomy_id':'term_taxonomy_id',
'wp_term_relationships.term_order':'term_order',

'wp_term_taxonomy.term_taxonomy_id':'term_taxonomy_id',
'wp_term_taxonomy.term_id':'term_id',
'wp_term_taxonomy.taxonomy':'taxonomy',
'wp_term_taxonomy.description':'description',
'wp_term_taxonomy.parent':'parent',
'wp_term_taxonomy.count':'count',

'wp_termmeta.meta_id':'meta_id',
'wp_termmeta.term_id':'term_id',
'wp_termmeta.meta_key':'meta_key',
'wp_termmeta.meta_value':'meta_value',

'wp_terms.term_id':'term_id',
'wp_terms.name':'name',
'wp_terms.slug':'slug',
'wp_terms.term_group':'term_group',

'wp_usermeta.umeta_id':'umeta_id',
'wp_usermeta.user_id':'user_id',
'wp_usermeta.meta_key':'meta_key',
'wp_usermeta.meta_value':'meta_value',

'wp_users.ID':'ID',
'wp_users.user_login':'user_login',
'wp_users.user_pass':'user_pass',
'wp_users.user_nicename':'user_nicename',
'wp_users.user_email':'user_email',
'wp_users.user_url':'user_url',
'wp_users.user_registered':'user_registered',
'wp_users.user_activation_key':'user_activation_key',
'wp_users.user_status':'user_status',
'wp_users.display_name':'display_name',

};

//export type RestTables = "$all_table_names_types";

export type RestTableInterfaces = iCarbon_Comments | iCarbons | iCarbon_Feature_Group_References | iCarbon_Features | iCarbon_Group_References | iCarbon_Groups | iCarbon_Location_References | iCarbon_Locations | iCarbon_Photos | iCarbon_Reports | iCarbon_User_Followers | iCarbon_User_Groups | iCarbon_User_Messages | iCarbon_User_Sessions | iCarbon_User_Tasks | iCarbon_Users | iCreation_Logs | iHistory_Logs | iSessions | iWp_Commentmeta | iWp_Comments | iWp_Links | iWp_Options | iWp_Postmeta | iWp_Posts | iWp_Term_Relationships | iWp_Term_Taxonomy | iWp_Termmeta | iWp_Terms | iWp_Usermeta | iWp_Users;

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

