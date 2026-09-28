jQuery(document).ready(function($) {
    var sectionsContainer = $('.mcp-sections-container');
    var sectionTemplate = $('.mcp-section-template').html();
    var sectionIndex = sectionsContainer.find('.mcp-section').length;

    $('#mcp-add-section').on('click', function() {
        var newSectionHtml = sectionTemplate.replace(/__INDEX__/g, sectionIndex);
        var newSection = $(newSectionHtml);
        sectionsContainer.append(newSection);

        var newEditorId = 'mcp_sections_' + sectionIndex + '_content';
        wp.editor.initialize(newEditorId, {
            tinymce: {
                wpautop: true,
                plugins : 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
                toolbar1: 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                toolbar2: 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help'
            },
            quicktags: true
        });

        sectionIndex++;
    });

    sectionsContainer.on('click', '.mcp-remove-section', function() {
        var section = $(this).closest('.mcp-section');
        var editorId = section.find('.wp-editor-area').attr('id');
        wp.editor.remove(editorId);
        section.remove();
    });

    // Dependent dropdowns
    var postType = $('body').hasClass('post-type-topic') ? 'topic' : ($('body').hasClass('post-type-flashcard') ? 'flashcard' : '');

    if (postType === 'topic' || postType === 'flashcard') {
        $('#mcp_topic_course_id').on('change', function() {
            var courseId = $(this).val();
            var lessonDropdown = $('#mcp_lesson_id');
            lessonDropdown.empty().append('<option value="">— Select —</option>');
            if (postType === 'flashcard') {
                $('#mcp_topic_id').empty().append('<option value="">— Select —</option>');
            }

            if (courseId) {
                $.ajax({
                    url: mcp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mcp_get_lessons_by_course',
                        course_id: courseId
                    },
                    success: function(response) {
                        if (response.success) {
                            $.each(response.data, function(index, lesson) {
                                lessonDropdown.append('<option value="' + lesson.id + '">' + lesson.title + '</option>');
                            });
                        }
                    }
                });
            }
        });

        if (postType === 'flashcard') {
            $('#mcp_lesson_id').on('change', function() {
                var lessonId = $(this).val();
                var topicDropdown = $('#mcp_topic_id');
                topicDropdown.empty().append('<option value="">— Select —</option>');

                if (lessonId) {
                    $.ajax({
                        url: mcp_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'mcp_get_topics_by_lesson',
                            lesson_id: lessonId
                        },
                        success: function(response) {
                            if (response.success) {
                                $.each(response.data, function(index, topic) {
                                    topicDropdown.append('<option value="' + topic.id + '">' + topic.title + '</option>');
                                });
                            }
                        }
                    });
                }
            });
        }
    }

    // Repeater for Flashcards
    var flashcardsContainer = $('.mcp-flashcards-container');
    var flashcardTemplate = $('.mcp-flashcard-template').html();
    var flashcardIndex = flashcardsContainer.find('.mcp-flashcard').length;

    $('#mcp-add-flashcard').on('click', function() {
        var newFlashcard = flashcardTemplate.replace(/__INDEX__/g, flashcardIndex);
        flashcardsContainer.append(newFlashcard);
        flashcardIndex++;
    });

    flashcardsContainer.on('click', '.mcp-remove-flashcard', function() {
        $(this).closest('.mcp-flashcard').remove();
    });


    // Icon picker logic for Section Type edit page
    if ($('body.post-type-section_type').length) {
        // Handle icon selection
        $('.mcp-icon-picker').on('click', 'i', function() {
            var iconClass = $(this).data('icon');
            $('#mcp_icon_class').val(iconClass);
            $('.mcp-icon-picker i').removeClass('selected');
            $(this).addClass('selected');
        });

        // Set the initial selected icon on page load
        var currentIcon = $('#mcp_icon_class').val();
        if (currentIcon) {
            $('.mcp-icon-picker i[data-icon="' + currentIcon + '"]').addClass('selected');
        }
    }
});
