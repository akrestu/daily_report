# SiGAP Documentation

Welcome to the SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) documentation. This folder contains comprehensive documentation for developers, administrators, and end users.

## Documentation Overview

### For End Users
- **[user_guide.md](user_guide.md)** - Complete guide for using the SiGAP system
  - Getting started
  - Creating and managing reports
  - Using the organization chart
  - Comments and notifications
  - Profile management

### For Administrators
- **[deployment_checklist.md](deployment_checklist.md)** - Step-by-step deployment guide
- **[troubleshooting.md](troubleshooting.md)** - Common issues and solutions

### For Developers
- **[technical_documentation.md](technical_documentation.md)** - System architecture and technical details
  - Core components
  - Database structure
  - Feature implementation
  - API endpoints
  - Security considerations
  - Performance optimizations
- **[development_setup.md](development_setup.md)** - Setting up development environment
- **[laravel_12_compliance.md](laravel_12_compliance.md)** - Laravel 12 specific implementation details
- **[api_documentation.md](api_documentation.md)** - API reference and usage
- **[project_structure.md](project_structure.md)** - Codebase organization and file structure

### Feature-Specific Documentation
- **[notification_improvements.md](notification_improvements.md)** - Notification system details
- **[reports_cleanup_system.md](reports_cleanup_system.md)** - Automated report cleanup features
- **[timezone_configuration.md](timezone_configuration.md)** - Timezone handling (WIB/Asia Jakarta)

### Project Information
- **[rebranding_notes.md](rebranding_notes.md)** - Brand identity and UI/UX updates
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and change log

## Quick Start

### For New Users
1. Start with [user_guide.md](user_guide.md) to learn how to use the system
2. Refer to [troubleshooting.md](troubleshooting.md) if you encounter any issues

### For Administrators
1. Follow [deployment_checklist.md](deployment_checklist.md) for installation
2. Review [technical_documentation.md](technical_documentation.md) for system overview
3. Keep [troubleshooting.md](troubleshooting.md) handy for support

### For Developers
1. Set up your environment using [development_setup.md](development_setup.md)
2. Study [technical_documentation.md](technical_documentation.md) for architecture
3. Review [laravel_12_compliance.md](laravel_12_compliance.md) for Laravel specifics
4. Check [project_structure.md](project_structure.md) to understand the codebase
5. Refer to [api_documentation.md](api_documentation.md) for API integration

## System Overview

**SiGAP** is a comprehensive daily work activity reporting system built with Laravel 12. It features:

- ✅ Multi-level hierarchical approval workflow (5 levels + Admin)
- ✅ Real-time notifications and comments
- ✅ Organization chart visualization
- ✅ File attachment support (up to 3 per report)
- ✅ Import/Export functionality
- ✅ Mobile-responsive design
- ✅ Role-based access control
- ✅ Indonesian timezone (WIB) support

## Current Version

**Version**: 1.1.0 (November 2024)

### Recent Updates
- Custom SiGAP logo implementation
- Organization chart UI improvements
- Report details tab reordering for better UX
- Enhanced documentation

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.1+
- **Frontend**: Blade, Livewire, Bootstrap 5, Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Tools**: Vite, Composer, NPM
- **Icons**: FontAwesome
- **Charts**: Chart.js

## Documentation Standards

All documentation in this folder follows these principles:
- Clear and concise language
- Step-by-step instructions where applicable
- Code examples when relevant
- Screenshots for UI-related guides (where available)
- Regular updates to reflect system changes

## Getting Help

1. **Check Documentation**: Most questions are answered in these docs
2. **Troubleshooting Guide**: See [troubleshooting.md](troubleshooting.md)
3. **Technical Issues**: Review [technical_documentation.md](technical_documentation.md)
4. **Feature Questions**: Refer to [user_guide.md](user_guide.md)

## Contributing to Documentation

When updating documentation:
1. Keep language clear and simple
2. Update CHANGELOG.md for significant changes
3. Ensure cross-references are accurate
4. Test all code examples
5. Update the "Last Updated" date

## Support

For technical support or questions not covered in documentation:
- Review the troubleshooting guide first
- Check Laravel 12 documentation for framework-specific issues
- Consult the technical documentation for architecture questions

---

**Last Updated**: November 12, 2024
**Documentation Version**: 1.1.0
**System Version**: 1.1.0
