# SiGAP - Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2024-11-12

### Added
- Custom SiGAP logo (Sigap.png) to replace FontAwesome icons
- Logo implementation in login screen with circular container
- Logo implementation in sidebar navigation header
- Enhanced organization chart with detailed role legend
- Comprehensive documentation updates across all docs files

### Changed
- **Organization Chart**: Removed Administrator from Role Legend to focus on operational hierarchy
  - Admin role still exists for system management
  - Legend now displays only Level 1-5 for operational clarity
- **Report Details**: Reordered tabs for improved user experience
  - Old order: Desc → Comments → Remarks
  - New order: Desc → Remarks → Comments
  - Rationale: Remarks complement Description, Comments are follow-up discussions
- Updated technical_documentation.md with UI/UX design section
- Updated user_guide.md with organization chart and report viewing details
- Updated rebranding_notes.md with latest UI improvements

### Improved
- User experience with more logical information flow in report details
- Visual consistency with custom logo across login and navigation
- Documentation clarity with detailed explanations of system features

## [1.0.0] - 2024-10-15

### Added
- Initial release of SiGAP system
- Daily report management with multi-level approval workflow
- User management with role-based access control
- Department organization structure
- Notification system for approvals and comments
- File attachment support (up to 3 files per report)
- Import/export functionality with Excel
- Organization chart visualization
- Real-time comments on reports
- Profile management with picture upload

### Features
- **Authentication**: Secure login with user_id and password
- **Dashboard**: Overview of reports with statistics
- **Reports**: Create, edit, view, approve/reject daily work reports
- **Hierarchy**: 5-level organizational structure (Level 1-5) plus Admin
- **Approval Workflow**: Multi-step approval based on user levels
- **Comments**: Discussion threads on reports
- **Notifications**: Real-time alerts for report actions
- **Search & Filter**: Advanced filtering by date, status, department
- **Mobile Responsive**: Optimized for mobile, tablet, and desktop

### Technical Stack
- Laravel 12
- PHP 8.1+
- MySQL/PostgreSQL
- Livewire for dynamic components
- Blade templating
- Bootstrap 5 + Tailwind CSS
- Alpine.js for interactivity
- Vite for asset compilation
- FontAwesome icons
- Chart.js for visualizations

---

## Version History Summary

- **1.1.0** (Nov 2024): UI/UX improvements, logo implementation, documentation updates
- **1.0.0** (Oct 2024): Initial release with full feature set

---

## Future Roadmap

### Planned Features
- [ ] Advanced analytics and reporting dashboard
- [ ] Email notifications for approvals
- [ ] Mobile app (PWA enhancement)
- [ ] Document templates
- [ ] Approval delegation system
- [ ] Audit trail for all actions
- [ ] Multi-language support

### Under Consideration
- [ ] Integration with external calendar systems
- [ ] API for third-party integrations
- [ ] Advanced search with full-text indexing
- [ ] Report templates and automation
- [ ] Department-level analytics

---

**Maintained by**: SiGAP Development Team
**Documentation**: See `/docs` folder for detailed documentation
**Support**: Check troubleshooting.md for common issues
