<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'Carbon_Fields\\Carbon_Fields' => '/vendor/htmlburger/carbon-fields/core/Carbon_Fields.php',
                'Carbon_Fields\\Container' => '/vendor/htmlburger/carbon-fields/core/Container.php',
                'Carbon_Fields\\Container\\Broken_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Broken_Container.php',
                'Carbon_Fields\\Container\\Comment_Meta_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Comment_Meta_Container.php',
                'Carbon_Fields\\Container\\Condition\\Blog_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Blog_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Boolean_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Boolean_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Any_Contain_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Any_Contain_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Any_Equality_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Any_Equality_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Contain_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Contain_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Custom_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Custom_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Equality_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Equality_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Comparer\\Scalar_Comparer' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Comparer/Scalar_Comparer.php',
                'Carbon_Fields\\Container\\Condition\\Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Condition.php',
                'Carbon_Fields\\Container\\Condition\\Current_User_Capability_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Current_User_Capability_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Current_User_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Current_User_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Current_User_Role_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Current_User_Role_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Factory' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Factory.php',
                'Carbon_Fields\\Container\\Condition\\Post_Ancestor_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Ancestor_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Format_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Format_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Level_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Level_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Parent_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Parent_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Template_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Template_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Term_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Term_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Post_Type_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Post_Type_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Term_Ancestor_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Term_Ancestor_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Term_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Term_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Term_Level_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Term_Level_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Term_Parent_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Term_Parent_Condition.php',
                'Carbon_Fields\\Container\\Condition\\Term_Taxonomy_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/Term_Taxonomy_Condition.php',
                'Carbon_Fields\\Container\\Condition\\User_Capability_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/User_Capability_Condition.php',
                'Carbon_Fields\\Container\\Condition\\User_ID_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/User_ID_Condition.php',
                'Carbon_Fields\\Container\\Condition\\User_Role_Condition' => '/vendor/htmlburger/carbon-fields/core/Container/Condition/User_Role_Condition.php',
                'Carbon_Fields\\Container\\Container' => '/vendor/htmlburger/carbon-fields/core/Container/Container.php',
                'Carbon_Fields\\Container\\Fulfillable\\Fulfillable' => '/vendor/htmlburger/carbon-fields/core/Container/Fulfillable/Fulfillable.php',
                'Carbon_Fields\\Container\\Fulfillable\\Fulfillable_Collection' => '/vendor/htmlburger/carbon-fields/core/Container/Fulfillable/Fulfillable_Collection.php',
                'Carbon_Fields\\Container\\Fulfillable\\Translator\\Array_Translator' => '/vendor/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Array_Translator.php',
                'Carbon_Fields\\Container\\Fulfillable\\Translator\\Json_Translator' => '/vendor/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Json_Translator.php',
                'Carbon_Fields\\Container\\Fulfillable\\Translator\\Translator' => '/vendor/htmlburger/carbon-fields/core/Container/Fulfillable/Translator/Translator.php',
                'Carbon_Fields\\Container\\Nav_Menu_Item_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Nav_Menu_Item_Container.php',
                'Carbon_Fields\\Container\\Network_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Network_Container.php',
                'Carbon_Fields\\Container\\Post_Meta_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Post_Meta_Container.php',
                'Carbon_Fields\\Container\\Repository' => '/vendor/htmlburger/carbon-fields/core/Container/Repository.php',
                'Carbon_Fields\\Container\\Term_Meta_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Term_Meta_Container.php',
                'Carbon_Fields\\Container\\Theme_Options_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Theme_Options_Container.php',
                'Carbon_Fields\\Container\\User_Meta_Container' => '/vendor/htmlburger/carbon-fields/core/Container/User_Meta_Container.php',
                'Carbon_Fields\\Container\\Widget_Container' => '/vendor/htmlburger/carbon-fields/core/Container/Widget_Container.php',
                'Carbon_Fields\\Datastore\\Comment_Meta_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Comment_Meta_Datastore.php',
                'Carbon_Fields\\Datastore\\Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Datastore.php',
                'Carbon_Fields\\Datastore\\Datastore_Holder_Interface' => '/vendor/htmlburger/carbon-fields/core/Datastore/Datastore_Holder_Interface.php',
                'Carbon_Fields\\Datastore\\Datastore_Interface' => '/vendor/htmlburger/carbon-fields/core/Datastore/Datastore_Interface.php',
                'Carbon_Fields\\Datastore\\Key_Value_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Key_Value_Datastore.php',
                'Carbon_Fields\\Datastore\\Meta_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Meta_Datastore.php',
                'Carbon_Fields\\Datastore\\Nav_Menu_Item_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Nav_Menu_Item_Datastore.php',
                'Carbon_Fields\\Datastore\\Network_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Network_Datastore.php',
                'Carbon_Fields\\Datastore\\Post_Meta_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Post_Meta_Datastore.php',
                'Carbon_Fields\\Datastore\\Term_Meta_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Term_Meta_Datastore.php',
                'Carbon_Fields\\Datastore\\Theme_Options_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Theme_Options_Datastore.php',
                'Carbon_Fields\\Datastore\\User_Meta_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/User_Meta_Datastore.php',
                'Carbon_Fields\\Datastore\\Widget_Datastore' => '/vendor/htmlburger/carbon-fields/core/Datastore/Widget_Datastore.php',
                'Carbon_Fields\\Event\\Emitter' => '/vendor/htmlburger/carbon-fields/core/Event/Emitter.php',
                'Carbon_Fields\\Event\\Listener' => '/vendor/htmlburger/carbon-fields/core/Event/Listener.php',
                'Carbon_Fields\\Event\\PersistentListener' => '/vendor/htmlburger/carbon-fields/core/Event/PersistentListener.php',
                'Carbon_Fields\\Event\\SingleEventListener' => '/vendor/htmlburger/carbon-fields/core/Event/SingleEventListener.php',
                'Carbon_Fields\\Exception\\Incorrect_Syntax_Exception' => '/vendor/htmlburger/carbon-fields/core/Exception/Incorrect_Syntax_Exception.php',
                'Carbon_Fields\\Field' => '/vendor/htmlburger/carbon-fields/core/Field.php',
                'Carbon_Fields\\Field\\Association_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Association_Field.php',
                'Carbon_Fields\\Field\\Broken_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Broken_Field.php',
                'Carbon_Fields\\Field\\Checkbox_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Checkbox_Field.php',
                'Carbon_Fields\\Field\\Color_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Color_Field.php',
                'Carbon_Fields\\Field\\Complex_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Complex_Field.php',
                'Carbon_Fields\\Field\\Date_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Date_Field.php',
                'Carbon_Fields\\Field\\Date_Time_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Date_Time_Field.php',
                'Carbon_Fields\\Field\\Field' => '/vendor/htmlburger/carbon-fields/core/Field/Field.php',
                'Carbon_Fields\\Field\\File_Field' => '/vendor/htmlburger/carbon-fields/core/Field/File_Field.php',
                'Carbon_Fields\\Field\\Footer_Scripts_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Footer_Scripts_Field.php',
                'Carbon_Fields\\Field\\Gravity_Form_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Gravity_Form_Field.php',
                'Carbon_Fields\\Field\\Group_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Group_Field.php',
                'Carbon_Fields\\Field\\Header_Scripts_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Header_Scripts_Field.php',
                'Carbon_Fields\\Field\\Hidden_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Hidden_Field.php',
                'Carbon_Fields\\Field\\Html_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Html_Field.php',
                'Carbon_Fields\\Field\\Image_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Image_Field.php',
                'Carbon_Fields\\Field\\Map_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Map_Field.php',
                'Carbon_Fields\\Field\\Media_Gallery_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Media_Gallery_Field.php',
                'Carbon_Fields\\Field\\Multiselect_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Multiselect_Field.php',
                'Carbon_Fields\\Field\\OEmbed_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Oembed_Field.php',
                'Carbon_Fields\\Field\\Predefined_Options_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Predefined_Options_Field.php',
                'Carbon_Fields\\Field\\Radio_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Radio_Field.php',
                'Carbon_Fields\\Field\\Radio_Image_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Radio_Image_Field.php',
                'Carbon_Fields\\Field\\Rich_Text_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Rich_Text_Field.php',
                'Carbon_Fields\\Field\\Scripts_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Scripts_Field.php',
                'Carbon_Fields\\Field\\Select_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Select_Field.php',
                'Carbon_Fields\\Field\\Separator_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Separator_Field.php',
                'Carbon_Fields\\Field\\Set_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Set_Field.php',
                'Carbon_Fields\\Field\\Sidebar_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Sidebar_Field.php',
                'Carbon_Fields\\Field\\Text_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Text_Field.php',
                'Carbon_Fields\\Field\\Textarea_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Textarea_Field.php',
                'Carbon_Fields\\Field\\Time_Field' => '/vendor/htmlburger/carbon-fields/core/Field/Time_Field.php',
                'Carbon_Fields\\Helper\\Color' => '/vendor/htmlburger/carbon-fields/core/Helper/Color.php',
                'Carbon_Fields\\Helper\\Delimiter' => '/vendor/htmlburger/carbon-fields/core/Helper/Delimiter.php',
                'Carbon_Fields\\Helper\\Helper' => '/vendor/htmlburger/carbon-fields/core/Helper/Helper.php',
                'Carbon_Fields\\Libraries\\Sidebar_Manager\\Sidebar_Manager' => '/vendor/htmlburger/carbon-fields/core/Libraries/Sidebar_Manager/Sidebar_Manager.php',
                'Carbon_Fields\\Loader\\Loader' => '/vendor/htmlburger/carbon-fields/core/Loader/Loader.php',
                'Carbon_Fields\\Pimple\\Container' => '/vendor/htmlburger/carbon-fields/core/Pimple/Container.php',
                'Carbon_Fields\\Pimple\\ServiceProviderInterface' => '/vendor/htmlburger/carbon-fields/core/Pimple/ServiceProviderInterface.php',
                'Carbon_Fields\\Provider\\Container_Condition_Provider' => '/vendor/htmlburger/carbon-fields/core/Provider/Container_Condition_Provider.php',
                'Carbon_Fields\\REST_API\\Decorator' => '/vendor/htmlburger/carbon-fields/core/REST_API/Decorator.php',
                'Carbon_Fields\\REST_API\\Router' => '/vendor/htmlburger/carbon-fields/core/REST_API/Router.php',
                'Carbon_Fields\\Service\\Legacy_Storage_Service_v_1_5' => '/vendor/htmlburger/carbon-fields/core/Service/Legacy_Storage_Service_v_1_5.php',
                'Carbon_Fields\\Service\\Meta_Query_Service' => '/vendor/htmlburger/carbon-fields/core/Service/Meta_Query_Service.php',
                'Carbon_Fields\\Service\\REST_API_Service' => '/vendor/htmlburger/carbon-fields/core/Service/REST_API_Service.php',
                'Carbon_Fields\\Service\\Service' => '/vendor/htmlburger/carbon-fields/core/Service/Service.php',
                'Carbon_Fields\\Toolset\\Key_Toolset' => '/vendor/htmlburger/carbon-fields/core/Toolset/Key_Toolset.php',
                'Carbon_Fields\\Toolset\\WP_Toolset' => '/vendor/htmlburger/carbon-fields/core/Toolset/WP_Toolset.php',
                'Carbon_Fields\\Value_Set\\Value_Set' => '/vendor/htmlburger/carbon-fields/core/Value_Set/Value_Set.php',
                'Carbon_Fields\\Walker\\Nav_Menu_Item_Edit_Walker' => '/vendor/htmlburger/carbon-fields/core/Walker/Nav_Menu_Item_Edit_Walker.php',
                'Carbon_Fields\\Widget' => '/vendor/htmlburger/carbon-fields/core/Widget.php',
                'Carbon_Fields\\Widget\\Widget' => '/vendor/htmlburger/carbon-fields/core/Widget/Widget.php',
                'DrewM\\MailChimp\\Batch' => '/vendor/drewm/mailchimp-api/src/Batch.php',
                'DrewM\\MailChimp\\MailChimp' => '/vendor/drewm/mailchimp-api/src/MailChimp.php',
                'DrewM\\MailChimp\\Webhook' => '/vendor/drewm/mailchimp-api/src/Webhook.php',
                'bbg\\wp\\common\\ajax' => '/bbg/wp/common/ajax.php',
                'bbg\\wp\\common\\base\\ajax' => '/bbg/wp/common/base/ajax.php',
                'bbg\\wp\\common\\base\\blobject' => '/bbg/wp/common/base/blobject.php',
                'bbg\\wp\\common\\base\\cron' => '/bbg/wp/common/base/cron.php',
                'bbg\\wp\\common\\base\\fields' => '/bbg/wp/common/base/fields.php',
                'bbg\\wp\\common\\base\\hook' => '/bbg/wp/common/base/hook.php',
                'bbg\\wp\\common\\base\\modal' => '/bbg/wp/common/base/modal.php',
                'bbg\\wp\\common\\base\\partial' => '/bbg/wp/common/base/partial.php',
                'bbg\\wp\\common\\debug' => '/bbg/wp/common/debug.php',
                'bbg\\wp\\common\\fields' => '/bbg/wp/common/fields.php',
                'bbg\\wp\\common\\fields\\serialized_post_meta' => '/bbg/wp/common/fields/serialized_post_meta.php',
                'bbg\\wp\\common\\fields\\serialized_term_meta' => '/bbg/wp/common/fields/serialized_term_meta.php',
                'bbg\\wp\\common\\fields\\serialized_theme_options' => '/bbg/wp/common/fields/serialized_theme_options.php',
                'bbg\\wp\\common\\fields\\serialized_user_meta' => '/bbg/wp/common/fields/serialized_user_meta.php',
                'bbg\\wp\\common\\hook' => '/bbg/wp/common/hook.php',
                'bbg\\wp\\common\\meta' => '/bbg/wp/common/meta.php',
                'bbg\\wp\\common\\newsletter' => '/bbg/wp/common/newsletter.php',
                'bbg\\wp\\common\\sitemap' => '/bbg/wp/common/sitemap.php',
                'bbg\\wp\\common\\social' => '/bbg/wp/common/social.php',
                'bbg\\wp\\common\\svg' => '/bbg/wp/common/svg.php',
                'bbg\\wp\\common\\terms' => '/bbg/wp/common/terms.php',
                'bbg\\wp\\common\\typetax' => '/bbg/wp/common/typetax.php',
                'bbg\\wp\\common\\upgrade' => '/bbg/wp/common/upgrade.php',
                'bbg\\wp\\common\\utility' => '/bbg/wp/common/utility.php'
            );
        }
        if (isset($classes[$class])) {
            require __DIR__ . $classes[$class];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd
