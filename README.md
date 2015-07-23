# SeeCMSv3
SeeCMS Content Management System

v3.4 release notes

This update includes the following fixes and improvements

Add ons
- Fixed styling bug with the add on list

ADFs
- Updated loadLinkDetails method to return whole object in addition to other data for download links
- Fixed scroll issue when selecting content
- Fixed bug with ADF link URLS which including a hyphen being truncated
- Fixed bug with ADF popup not loading in some circumstances
- Fixed bug with adfMediaFolder selection option failure where there were multiple fields of that type on one page
- Fixed bug with cascaded field not appearing on the main page as it should
- Fixed bug with removing an ADF image from a set
- Fixed bug with problem saving ADF set if a set was removed containing a tinymce

Analytics
- Added caching of previous monthly data
- Updated view to exclude items which don't now exist
- Updated view with various display improvements
- Fixed bug with previous monthly data not loading

Downloads
- Added support for downloads to to loaded inline
- Changed process order to check status before access
- Fixed bug with document status not working
- Fixed bug with redirect to login page not working for secured files
- Fixed bug with permission inheritance not applying correctly on uploaded files
- Fixed bug causing downloads not to track in analytics
- Fixed bug with uploader on old browsers

Hooks
- Added support for hooks

Media
- Added ADF support to media
- Added on the fly creation of missing media and removed the bulk creation on upload
- Added selectable field in DB to allow image sizes to be hidden from user selection
- Fixed bug stopping media edits saving
- Fixed bug with uploader on old browsers

Pages
- Added nextPage method
- Added missing option to remove a page redirect
- Fixed bug with exclude ADF option for pages
- Fixed bug with page reordering within the same list/parent

Posts
- Added ADF support to posts
- Added option to feed method to allow results to be paged
- Added option to feed method to allow ordering
- Added id into data returned by post feed method
- Added support for forcing a specific template for posts in a category
- Added missing custom edit fields which were missing from posts
- Updated feed method to include post categories and whole post object in return data
- Updated CMS view to show eventStartDate rather than date if it's set
- Fixed bug saving undated posts

Rich Text Editor
- Added option to allow custom CSS in TinyMCE
- Fixed bug with inserting original image size
- Fixed bug where rich text editor popup was attached to content part which didn't appear on the page meaning content wasn't editable

Search
- Added websiteUsers to admin search

SeePHP / CORE
- Updated core include to use require_once instead of include to overcome chain include problems in some third party plugins

SeePHP / Email
- Added option to allow email attachment content to be passed directly, rather than it having to be a file on disk
- Improved sendHTMLEmail method with updated headers/boundaries to resolve issues with some email systems

SeePHP / Format
- Fixed problem with date method on Unix servers caused by strtotime returning negative integers where the dates are pre Unix Epoch

SeePHP / Form Process
- Add a setting ['introHTML'] to allow extra content to be sent in the message
- Removed the -SeeFormProcess-sendByEmail- input from the email
- Fixed bug with bad HTML in message

SeePHP / HTML
- Updated meta method to stop duplicate meta tags and allow overrides

SeePHP / HTML Form
- Fixed bug with action path when page was loaded without ending /

SeePHP / Image
- Fixed bug with PNG transparency

SeePHP / Social
- Updated getTweets method to include tweet id and media url in return data

Website users
- Fixed bug with loading ADFs for a website user on login
- Fixed bug when updating website user caused error when any field was not included in the POST data
- Fixed bug with removing users from groups
- Fixed problem with login redirects being unset where the login page contained a GET parameter

-- 

Other
- Added doc and xlsx icons
- Added XML Sitemap as standard /xmlsitemap/
- Added generator, description and keywords meta by default to pages/posts
- Changed name of IIS rewrite rule to SeeCMS (Only on new installs)
- Fix minor issue in breadcrumb html 
