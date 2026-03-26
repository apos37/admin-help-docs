<?php
/**
 * FAQ Tab Loader
 */

namespace PluginRx\AdminHelpDocs;

if ( ! defined( 'ABSPATH' ) ) exit;

class FAQ {

    /**
     * The single instance of the class
     *
     * @var self|null
     */
    private static ?FAQ $instance = null;


    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function instance() : self {
        return self::$instance ??= new self();
    } // End instance()


    /**
     * Render the tab
     */
    public function render_tab() {
        ?>
        <div class="helpdocs-full-width-box">
            <h3>How do I add a document?</h3>
            <ul>
                <li>Head over to the <a href="<?php echo esc_url( Bootstrap::tab_url( 'manage' ) ); ?>">Manage</a> tab and add a new Help Document.</li>
                <li>Include the title and content for the document.</li>
                <li>If you want to add a separate description for the document so you can reference what it is for, add it the Description section.</li>
                <li>The "Location" box is where you specify where you want the document to show up.</li>
                <li><strong>Site Location</strong> includes all pages that are in your admin menu:
                    <ul>
                        <li><strong>Main Documentation Page:</strong> <?php echo esc_attr( Helpers::get_menu_title() ); ?> > <a href="<?php echo esc_url( Bootstrap::tab_url( 'documentation' ) ); ?>">Documentation</a></li>
                        <li><strong>Admin Bar Menu:</strong> Added to your top admin bar if enabled in <a href="<?php echo esc_url( Bootstrap::tab_url( 'settings' ) ); ?>">Settings</a></li>
                        <li><strong>WordPress Dashboard:</strong> Adds a meta box to your WordPress <a href="<?php echo esc_url( Bootstrap::admin_url( 'index.php' ) ); ?>">dashboard</a></li>
                        <li><strong>Function: admin_help_doc( id ):</strong> Developers can use this function to display a specific help document by its ID.</li>
                        <li><strong>Post/Page Edit Screen:</strong> The screen where you edit posts, pages, and other custom post types</li>
                        <li><strong>Post/Page Admin List Screen:</strong> The screen that lists all of your posts, pages, and other custom post types</li>
                        <li><strong><em>Other items from your menu...</em></strong></li>
                        <li><strong>Other/Custom Page:</strong> Use this if the screen you want isn't listed or isn't working properly</li>
                    </ul>
                </li>
                <li><strong>Page Location</strong> specifies the exact location on the screen where the document should appear.
                    <ul>
                        <li><strong>Contextual Help Tab:</strong> Shows up in the Help tab at the top-right of the screen</li>
                        <li><strong>Top:</strong> Displays at the top of the page - you can change the top location placement in settings</li>
                        <li><strong>Bottom:</strong> Displays at the bottom of the page</li>
                        <li><strong>Side:</strong> Displays at the top of the sidebar on the post edit screen only</li>
                        <li><strong>Element:</strong> Developers can display the doc immediately after a specific element on the page - enter the CSS selector - does not include logo and title like other docs</li>
                    </ul>
                </li>
                <li>If you cannot find a menu item in the Site Location dropdown, then it is probably a custom post type; therefore, try selecting one of the Post/Page screens to see if it shows up in the list of post types.</li>
                <li>Publish the document and look for it in the location you set. That's it! :)</li>
            </ul>

            <br><br>
            <h3>Why isn't my document showing up?</h3>
            <ul>
                <li>When adding or updating a document, make sure the "Location" box is filled out correctly. By default, the site location is set to "None" so that you don't accidentally post something you didn't mean to. Furthermore, if you are using Post/Page screens, be sure you are using the setting the correct post type(s); these can be confusing sometimes.</li>
                <li><em>Note: it is recommended to test adding a document to common locations to ensure that you're setting everything correctly.</em></li>
            </ul>

            <br><br>
            <h3>How do I use the same documentation across multiple sites?</h3>
            <ul>
                <li>On the site that has the document you want to share, edit the document and set "Allow Public" to "Yes". Alternatively, you may allow all of your documents to be public by default from <a href="<?php echo esc_url( Bootstrap::tab_url( 'settings' ) ); ?>">Settings</a>.</li>
                <li>On the site in which you want to display the document, go to the <a href="<?php echo esc_url( Bootstrap::tab_url( 'imports' ) ); ?>">Imports</a> tab, add a new import, and enter the site URL of the site the document is coming from. Then update the import.</li>
                <li>You may then choose to automatically feed all of the documents that are public from the other site, or you can feed specific documents only. You also have the option to import it locally so you don't have to feed it remotely.</li>
            </ul>

            <br>
            <h3>How do I add documents to the admin bar menu?</h3>
            <ul>
                <li>First, enable the admin bar menu quick link from <a href="<?php echo esc_url( Bootstrap::tab_url( 'settings' ) ); ?>">Settings</a>.</li>
                <li>From the document edit screen, choose "Admin Bar Menu" from the Site Location dropdown, and then save it. That's it!</li>
                <li>The submenu item will display as "Document Title — Document Content" with all html tags stripped out.</li>
                <li>If you just want to link the menu item to a page, paste only the URL in the content box without anything else.</li>
            </ul>

            <br><br>
            <h3>The Gutenberg editor doesn't allow the contextual help tab to be shown; can I still add help docs?</h3>
            <ul>
                <li>Yes, if you choose contextual help from the Page Location dropdown, a "Help" button will appear at the top right of the Gutenberg editor.</li>
            </ul>

            <br><br>
            <h3>How do I change the order of the documents on the main documentation page?</h3>
            <ul>
                <li>You can do so by dragging the menu items in the left column up or down.</li>
            </ul>

            <br><br>
            <h3>How do I add documents to folders on the main documentation page?</h3>
            <ul>
                <li>Edit a document.</li>
                <li>Navigate to the "Folder" meta box.</li>
                <li>Add a new folder if one doesn't exist.</li>
                <li>Select the folder you want to put it in.</li>
                <li>You may also manage folders by clicking on the <a href="<?php echo esc_url( Bootstrap::tab_url( 'folders' ) ); ?>">Folders</a> tab.</li>
                <li>You may also drag and drop documents into different folders from the main documentation page.</li>
                <li>Import feeds cannot be added to folders since they cannot be assigned taxonomies. You must clone them onto your site to add them to folders.</li>
            </ul>

            <br><br>
            <h3>If I delete a folder with documents, will I lose all of the documents inside the folder?</h3>
            <ul>
                <li>No. Deleting a folder simply removes the documents from the folder and places them outside of the folder area on the main documentation page. If you want to delete all of the documents inside a folder, the easiest way to do so is to go to the <a href="<?php echo esc_url( Bootstrap::tab_url( 'manage' ) ); ?>">Manage</a> tab, filter by folder, delete all of documents in that folder, <em>then</em> delete the folder from the <a href="<?php echo esc_url( Bootstrap::tab_url( 'folders' ) ); ?>">Folders</a> tab.</li>
            </ul>

            <br><br>
            <h3>How do I display a shortcode without executing it?</h3>
            <ul>
                <li>
                    <strong>Primary Method:</strong> Wrap your shortcode within the enclosing tags. This is the most reliable way to handle complex shortcodes.
                    <br />
                    Usage: <code>&#91;dont_do_shortcode&#93;&#91;your_shortcode_here&#93;&#91;/dont_do_shortcode&#93;</code>
                </li>
                <li>
                    <strong>Legacy Method:</strong> You may also pass the content as an attribute. When using this method, replace the square brackets of your target shortcode with curly braces <code>{ }</code>.
                    <br />
                    Usage: <code>&#91;dont_do_shortcode content='{shortcode_name param="value"}'&#93;</code>
                </li>
                <li>
                    <strong>Important:</strong> Do <strong>NOT</strong> mix these two methods. Use either the enclosing tags or the <code>content</code> attribute. Combining them will cause the shortcode to break.
                </li>
                <li>
                    <strong>Note:</strong> When using the legacy attribute method, ensure you use single quotes (<code>'</code>) for the <code>content</code> parameter so you can safely use double quotes (<code>"</code>) inside the shortcode attributes.
                </li>
                <li>
                    <strong>Options:</strong>
                    <ul>
                        <li>Disable click-to-copy functionality by adding <code>click_to_copy="false"</code>.</li>
                        <li>Change the wrapper from a code block to a standard span by adding <code>code="false"</code>.</li>
                    </ul>
                </li>
            </ul>

            <br><br>
            <h3>How do I add custom CSS to documents?</h3>
            <ul>
                <li>As of version 2.0, the old <code>[helpdocs_css]</code> shortcode is deprecated.</li>
                <li>Add your CSS from the Settings tab.</li>
            </ul>
        <?php
    } // End render_tab()

    
    /**
     * Prevent cloning and unserializing
     */
    public function __clone() {}
    public function __wakeup() {}

}


FAQ::instance();