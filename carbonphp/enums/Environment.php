<?php

namespace CarbonPHP\Enums;

enum Environment : int
{
    case LOCAL = 0;             // Local development (on a personal machine)
    case DEVELOPMENT = 1;       // Shared development environment
    case STAGING = 2;           // Pre-production environment (for testing)
    case TESTING = 3;           // Automated tests or CI/CD pipelines
    case PRODUCTION = 4;        // Live production environment
    case MAINTENANCE = 5;       // Maintenance mode (e.g., database migrations)
    case DEBUG = 6;             // Debugging mode, often used internally
    case DEMO = 7;              // Public demo/sandbox environment
    case DISASTER_RECOVERY = 8; // Backup environment in case of failures
}
