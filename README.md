# SeeCMSv3
SeeCMS Content Management System

v3.3 release notes

This update includes the following fixes and improvements

ADFs
- Fixed minor issues with mediaFolder and link types

Media
- Added support for using the GD image filter function, using the imagesize 'settings' field
- Added support for embedding MP4 video

Pages
- Bug fix / page title field wasn't reset after a new page was created
- Bug fix / new pages now inherit the template from their parent when they are created, new primary pages take the first template in the list.
- Improved layout of pages tree

Posts
- Bug fix / after second save of the post the thumbnail image was lost
- Bug fix / when deleting posts associated content wasn't all deleted
- Bug fix / when deleting post folders the system left orphaned posts

Rich Text Editor
- Bug fix / table properties could not be updated

Website users
- Added jobtitle field
- Implement user activation/deactivation options (Requires a manual update to the admin user role to allow admin user access to this feature)

--

Other
- Allowed multiple plugins to manage HTML output
