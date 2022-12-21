<style>
ul {
    list-style: square;
    padding: revert;
}
ul li {
    padding-inline-start: 1ch;
}
</style>

<?php include 'header-page.php'; ?>

<br>
<h3>How do I add help docs to the admin bar menu?</h3>
<ul>
    <li>First, enable the admin bar menu quick link from <a href="<?php echo esc_url( helpdocs_plugin_options_path( 'settings' ) ); ?>">settings</a>.</li>
    <li>From the document edit screen, choose "Admin Bar Menu" from the Site Location dropdown, and then save it. That's it!</li>
    <li>The submenu item will display as "Document Title — Document Content" with all html tags stripped out.</li>
    <li>If you just want to link the menu item to a page, paste only the URL in the content box without anything else.</li>
</ul>

<br><br>
<h3>Gutenberg doesn't allow the contextual help tab to be shown; can I still add it?</h3>
<ul>
    <li>Yes, if you choose contextual help from the Page Location dropdown, a "Help" button will appear at the top right of the Gutenberg editor.</li>
</ul>

<br><br>
<h3>Can I reset the positions of meta boxes to the default to see how it will show up for all users?</h3>
<ul>
    <li>Yes, options for resetting your meta boxes have been added to <a href="/<?php echo esc_attr( HELPDOCS_ADMIN_URL ); ?>/profile.php#<?php echo esc_attr( HELPDOCS_TEXTDOMAIN ); ?>">your profile</a>.</li>
</ul>

<br><br>
<h3>How do I use the same documentation across multiple sites?</h3>
<ul>
    <li>We are working on adding easier functionality for this, but for now you can export/import them just like you would any other post type.</li>
</ul>