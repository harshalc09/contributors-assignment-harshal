<?php
/*
Plugin Name: contributors assignment harshal
Plugin URI: http://mobwebdeveloper.com/
Description: Listing contributors to the post, assignment plugin for rtcamp, By harshal Chaudhari.
Author: Harshal Chaudhari
Author URI: http://mobwebdeveloper.com/
Version: 1.0.0
*/

//Add meta box to post page 
function cah_add_meta_box() {
    add_meta_box( 'cah_meta', 'Post Contributors', 'cah_meta_box_callback', 'post', 'side','high' );
}

// draw meta box call back
function cah_meta_box_callback( $post ) {
    wp_nonce_field( 'cah_meta_box', 'cah_meta_box_nonce' );

    //$allUsers = all the user from wordpress.
    $allUsers = get_users();

    //$contributorsList = List of all contributors id for that post.(value from post_meta table for key "cah_contributors".)
    $contributorsList = get_post_meta( $post->ID, 'cah_contributors', true );

    ?>
    <div class="container" >
        <? foreach($allUsers as $user){?>
            <input type="checkbox" name='contributor<? echo $user->ID ?>'
                <?
                if($contributorsList[0]!='')                                //Checks post_meta "cah_contributors" is already set or not.
                    if (in_array($user->ID,$contributorsList ))             //Checks user from all user list is already contributors or not.
                        echo 'checked="checked"';                           //if user from all user list is already contributors then marked as checked.
                ?> >
                <?
                        echo $user->user_login
                ?>
                 </input></br>
        <? } ?>
    </div>
    <?

}
function cah_save_meta_box_data($post_id, $post) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['cah_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['cah_meta_box_nonce'], 'cah_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    //$allUsers = all the user from wordpress.
    $allUsers = get_users();

    //$selectedUser = all checked user list.
    $selectedUser=array();

    foreach($allUsers as $user){
        if (isset($_POST["contributor".$user->ID])) { //check if particular users check box is checked.
            array_push($selectedUser,$user->ID);
        }
    }
    // Update the meta field in the database.
    update_post_meta( $post_id, 'cah_contributors', $selectedUser );
}


//extract url of avatar from get_avatar returns html.
function cah_get_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}


// call back to the action the_content.
// append the Contributor List (HTML) at the last of post content.
// which will dialled on front end at the bottom of post content.
function cah_display_authors($content){
    //$postId = Current Post id
    $postId=get_the_ID();

  //$contributorHtml = contains HTML as string of contributor avatar image , Login name , and link to user link.
    $contributorHtml="";

    //$contributorsList = List of all contributors id for that post.(value from post_meta table for key "cah_contributors".)
    $contributorsList = get_post_meta( $postId, 'cah_contributors', true );

    if($contributorsList[0]!=''){
        $contributorHtml="<div class='contributorList'><div class='headingContributor'>Contributors</div>";
        foreach($contributorsList as $userId){
            $user_info = get_userdata($userId);
            $contributorHtml=$contributorHtml." <div class='contributorUser'><img src='".  cah_get_avatar_url(get_avatar( $user_info->user_email, 32 ))."' class='avatarImg' alt='avatar'/> <a href='". get_author_posts_url( $userId ) ."'><b class='authorName'>". $user_info->user_login."</b></a></div>";

        }
        $contributorHtml=$contributorHtml."</div> </br>";
    }

    $content=$content . "</br>" .$contributorHtml ;
   return $content;
}

function cah_main_page() {
    ?>
    <h1>Contributors assignment By harshal Chaudhari</h1>
<?
}

//call back to "admin_menu" action.
function cah_menu() {
    //add menu to the admin dashboard.
    add_menu_page( 'Contributors Assignment Harshal', 'Contributors Meta', 'manage_options', 'cahMenu1', 'cah_main_page' );
}


//Include CSS
function cah_init(){
    wp_enqueue_style('ccudStyleBootstrap-css', plugins_url('/resource/css/contributors-assignment-harsha-style.css',__FILE__) );
}
add_filter( 'the_content', 'cah_display_authors' );
add_action('init', 'cah_init');

add_action( 'admin_menu', 'cah_menu' );
add_action( 'add_meta_boxes', 'cah_add_meta_box' );
add_action('publish_post', 'cah_save_meta_box_data',100,2);
add_action('post_updated', 'cah_save_meta_box_data',100,2);

?>