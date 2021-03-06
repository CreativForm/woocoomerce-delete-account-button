<?php if (!defined('WPINC') || !defined('ABSPATH')) die("Don't try to trick us. We know who you are!");
/*
 * Woocommerce Delete Account Button
 *
 * This class allows non-admin users to delete their profile
 *
 * @author Ivijan-Stefan Stipic <creativform@gmail.com>
 * @ver 1.0.0
 * @url http://creativform.com
*/
if(!class_exists('Woocommerce_Account_Delete')) :
	class Woocommerce_Account_Delete
	{
		function __construct(){
			if(is_user_logged_in() && !current_user_can( 'manage_options' ))
			{
				add_action( 'init', array($this, 'endpoints') );
				add_action( 'init', array($this, 'delete_account'),10,3 );
				add_filter( 'query_vars', array($this, 'query_vars'), 0 );
				add_action( 'after_switch_theme', array($this, 'flush_rewrite_rules') );
				add_filter( 'woocommerce_account_menu_items', array($this, 'my_account_menu_items') );
				add_action( 'woocommerce_account_delete-account_endpoint', array($this, 'endpoint_content') );
				add_action( 'woocommerce_after_edit_account_form', array($this, 'edit_account') );
			}
		}
		
		
		/*
		* Custom Endpoint
		* @since 1.0.0
		*/
		function endpoints() {
			add_rewrite_endpoint( 'delete-account', EP_ROOT | EP_PAGES );
		}
		
		
		/*
		* POST action perform delete account
		* @since 1.0.0
		*/
		function delete_account() {
			if(
				is_user_logged_in() 
				&& !current_user_can( 'manage_options' ) 
				&& isset($_POST['delete-account-id'])
				&& isset($_POST['delete-account']) 
				&& $_POST['delete-account'] == 'true'
				&& $_POST['delete-account-id'] == get_current_user_id()
			)
			{
				include("./wp-admin/includes/user.php" );
				$user_id = get_current_user_id();
				$meta = get_user_meta( $user_id );

				// Delete user's meta
				foreach ( $meta as $key => $val ) {
					delete_user_meta( $user_id, $key );
				}
			
				// Destroy user's session
				wp_logout();
			
				// Delete the user's account
				$deleted = wp_delete_user( $user_id );
			
				if ( $deleted ) {
					wc_add_notice(__('Account deleted. Thank you for using our service.'));
				} else {
					wc_add_notice(__('Account can\'t be deleted. Call system administrator or technical support.'), 'error');
				}
			}
		}
		
		
		/*
		* Query variable
		* @since 1.0.0
		*/
		function query_vars( $vars ) {
			$vars[] = 'delete-account';
			return $vars;
		}
		
		
		/*
		* Rewrite rules
		* @since 1.0.0
		*/
		function flush_rewrite_rules() {
			flush_rewrite_rules();
		}
		
		
		/*
		* Delete account link inside my account menu
		* @since 1.0.0
		*/
		function my_account_menu_items( $items ) {
			//$items['delete-account'] = __( 'Delete Account' );			
			return $items;
		}
		
		
		/*
		* Delete account button inside edit account page
		* @since 1.0.0
		*/
		function edit_account(){ ?>
            <div class="mt-3 text-right text-danger">
                <a href="<?php 
                if ( '' != get_option('permalink_structure') ) {
                    $read = user_trailingslashit(get_permalink( get_option('woocommerce_myaccount_page_id') ) . '/delete-account');
                } else {
                    $read = add_query_arg( 'delete-account', '', get_permalink( get_option('woocommerce_myaccount_page_id') ) );
                }
                
                echo $read ?>" class="text-danger">Delete Account</a>
            </div>
        <?php }
		
		/*
		* ENDPOINT: Page content
		* @since 1.0.0
		*/
		function endpoint_content() { ?>
<div class="card">
    <div class="card-header">
        <?php _e('Delete your user account and personal data'); ?>
    </div>
    <div class="card-body">
        <p class="card-text"><?php _e('Have you decided to delete your account? There are no problems but you must know that when you start this process, <strong>THERE IS NO BACK</strong>. All your activities, references, private and public information, affiliate informations, etc. will be permanently deleted WITHOUT the possibility to return back.'); ?></p>
        <p class="card-text"><strong><?php _e('Think twice before deleting the profile.'); ?></strong></p>
        <form method="post" name="account-delete" id="form-delete-account">
            <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="btn btn-outline-secondary"><?php _e('I CHANGE MY MIND'); ?></a> 
            <button type="submit" class="btn btn-danger" id="delete-profile"><?php _e('DELETE PROFILE'); ?></button>
            <input type="hidden" name="delete-account-id" value="<?php echo get_current_user_id(); ?>" />
            <input type="hidden" name="delete-account" value="true" />
        </form>
    </div>
</div>
		<?php }
	}
endif;
new Woocommerce_Account_Delete();
