<?php
/**
 * ECM Theme — comments.php
 * قالب التعليقات
 *
 * @package ecm-theme
 */

// Don't load if accessed directly
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="ecm-comments-area">

    <?php if ( have_comments() ) : ?>

        <h3 style="
            font-family: 'Orbitron', 'Cairo', sans-serif;
            font-size: 14px;
            letter-spacing: 3px;
            color: var(--ecm-white);
            text-transform: uppercase;
            margin-bottom: 32px;
        ">
            <?php
            printf(
                'التعليقات (%d)',
                get_comments_number()
            );
            ?>
        </h3>

        <ol class="comment-list" style="list-style: none; padding: 0; margin: 0;">
            <?php
            wp_list_comments( [
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 48,
                'callback'    => 'ecm_comment_template',
            ] );
            ?>
        </ol>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
            <nav style="margin-top: 24px; display: flex; justify-content: space-between; font-size: 13px;">
                <div><?php previous_comments_link( '← أقدم' ); ?></div>
                <div><?php next_comments_link( 'أحدث →' ); ?></div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p style="color: var(--ecm-grey-mid); font-size: 14px; padding: 24px 0;">
            التعليقات مغلقة.
        </p>
    <?php endif; ?>

    <!-- Comment Form -->
    <?php
    comment_form( [
        'title_reply'          => 'اترك تعليق',
        'title_reply_before'   => '<h3 style="font-family:\'Orbitron\',monospace; font-size:14px; letter-spacing:3px; color:var(--ecm-white); text-transform:uppercase; margin-bottom:24px;">',
        'title_reply_after'    => '</h3>',
        'comment_notes_before' => '<p style="font-size:13px; color:var(--ecm-grey-mid); margin-bottom:16px;">' . esc_html__( 'البريد الإلكتروني لن يتم نشره.', 'ecm-theme' ) . '</p>',
        'label_submit'         => 'نشر التعليق',
        'submit_button'        => '<button type="submit" class="ecm-btn-primary" id="%2$s" name="%1$s" style="margin-top:8px;">%4$s</button>',
        'comment_field'        => '<div style="margin-bottom:16px;"><textarea id="comment" name="comment" rows="5" required placeholder="اكتب تعليقك هنا..." style="
            width:100%;
            background: var(--ecm-bg-panel);
            border: 1px solid var(--ecm-border);
            border-radius: var(--ecm-radius-sm);
            padding: 14px;
            color: var(--ecm-grey-light);
            font-family: \'Cairo\', sans-serif;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.2s;
            outline: none;
        " onfocus="this.style.borderColor=\'var(--ecm-green)\'" onblur="this.style.borderColor=\'var(--ecm-border)\'"></textarea></div>',
    ] );
    ?>

</div><!-- #comments -->

<?php
/**
 * Custom comment callback
 */
if ( ! function_exists( 'ecm_comment_template' ) ) :
function ecm_comment_template( $comment, $args, $depth ) {
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class( '' ); ?> style="
        padding: 20px;
        margin-bottom: 16px;
        background: var(--ecm-bg-card);
        border: 1px solid var(--ecm-border);
        border-radius: var(--ecm-radius-md);
    ">
        <div style="display: flex; gap: 14px; align-items: flex-start;">

            <!-- Avatar -->
            <div style="flex-shrink: 0; border-radius: 50%; overflow: hidden; border: 2px solid var(--ecm-border);">
                <?php echo get_avatar( $comment, 48 ); ?>
            </div>

            <!-- Comment Body -->
            <div style="flex: 1;">
                <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">
                    <strong style="color: var(--ecm-white); font-size: 14px;">
                        <?php echo esc_html( get_comment_author() ); ?>
                    </strong>
                    <time style="font-size: 11px; color: var(--ecm-grey-mid); font-family: 'Orbitron', 'Cairo', sans-serif; letter-spacing: 1px;">
                        <?php echo esc_html( get_comment_date() ); ?>
                    </time>
                </div>

                <div style="color: var(--ecm-grey-light); font-size: 14px; line-height: 1.8;">
                    <?php comment_text(); ?>
                </div>

                <div style="margin-top: 10px; font-size: 12px;">
                    <?php
                    comment_reply_link( array_merge( $args, [
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '<span style="color: var(--ecm-green);">',
                        'after'     => '</span>',
                    ] ) );
                    ?>
                </div>
            </div>

        </div>
    </li>
    <?php
}
endif;
?>
