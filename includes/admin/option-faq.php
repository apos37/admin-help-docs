<style>
ul {
    list-style: square;
    padding: revert;
    padding-top: 10px;
    padding-bottom: 5px;
}
ul li {
    padding-inline-start: 1ch;
}
</style>

<?php include 'header-page.php'; ?>

<?php
$menu_title = helpdocs_menu_title();
$doc_link = helpdocs_plugin_options_path( 'documentation' );
$dashboard_link = home_url( HELPDOCS_ADMIN_URL.'/index.php' );
?>

<br><br>
<h3>How do I add a document?</h3>
<ul>
    <li>Head over to the <a href="<?php echo esc_url( home_url( HELPDOCS_ADMIN_URL.'/edit.php?post_type='.HELPDOCS_DOCUMENTATION::$post_type ) ); ?>">Manage</a> tab and add a new Help Document.</li>
    <li>Include the title and content for the document.</li>
    <li>If you want to add a separate description for the document so you can reference what it is for, add it the Description section.</li>
    <li>The "Location" box is where you specify where you want the document to show up.</li>
    <li><strong>Site Location</strong> includes all pages that are in your admin menu:
        <ul>
            <li><strong>Main Documentation Page:</strong> <?php echo esc_attr( $menu_title ); ?> > <a href="<?php echo esc_url( $doc_link ); ?>">Documentation</a></li>
            <li><strong>Admin Bar Menu:</strong> Added to your top admin bar if enabled in <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settings' ) ); ?>">settings</a></li>
            <li><strong>Dashboard:</strong> Adds a meta box to your WordPress <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'dashboard_link' ) ); ?>">dashboard</a></li>
            <li><strong>Post/Page Edit Screen:</strong> The screen where you edit posts, pages, and other custom post types</li>
            <li><strong>Post/Page Admin List Screen:</strong> The screen that lists all of your posts, pages, and other custom post types</li>
            <li><strong><em>Other items from your menu...</em></strong></li>
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
    <li>On the site that has the document you want to share, edit the document and set "Allow Public" to "Yes". Alternatively, you may allow all of your documents to be public by default from <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settings' ) ); ?>">settings</a>.</li>
    <li>On the site in which you want to display the document, go to the <a href="<?php echo esc_url( home_url( HELPDOCS_ADMIN_URL.'/edit.php?post_type='.HELPDOCS_IMPORTS::$post_type ) ); ?>">Imports</a> tab, add a new import, and enter the site URL of the site the document is coming from. Then update the import.</li>
    <li>You may then choose to automatically feed all of the documents that are public from the other site, or you can feed specific documents only. You also have the option to import it locally so you don't have to feed it remotely.</li>
</ul>

<br>
<h3>How do I add documents to the admin bar menu?</h3>
<ul>
    <li>First, enable the admin bar menu quick link from <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settings' ) ); ?>">settings</a>.</li>
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