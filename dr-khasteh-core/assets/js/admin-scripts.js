jQuery(document).ready(function($) {
    // Dependent dropdowns
    var postType = $('body').hasClass('post-type-topic') ? 'topic' : ($('body').hasClass('post-type-test') ? 'test' : '');

    if (postType === 'topic' || postType === 'test') {
        $('#mcp_course_id').on('change', function() {
            var courseId = $(this).val();
            var lessonDropdown = $('#mcp_lesson_id');
            lessonDropdown.empty().append('<option value="">— انتخاب —</option>');
            if (postType === 'test') {
                $('#mcp_topic_id').empty().append('<option value="">— انتخاب —</option>');
            }

            if (courseId) {
                $.ajax({
                    url: mtp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'dr_khasteh_get_lessons_by_course',
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

        if (postType === 'test') {
            $('#mcp_lesson_id').on('change', function() {
                var lessonId = $(this).val();
                var topicDropdown = $('#mcp_topic_id');
                topicDropdown.empty().append('<option value="">— انتخاب —</option>');

                if (lessonId) {
                    $.ajax({
                        url: mtp_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'dr_khasteh_get_topics_by_lesson',
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

    // Repeater for Tests
    var testsContainer = $('.mtp-tests-container');
    if (testsContainer.length) {
        var testTemplate = $('.mtp-test-template').html();
        var testIndex = testsContainer.find('.mtp-test').length;

        $('#mtp-add-test').on('click', function() {
            var newTestHtml = testTemplate.replace(/__INDEX__/g, testIndex);
            var newTest = $(newTestHtml);
            testsContainer.append(newTest);

            var newQuestionEditorId = 'mtp_tests_' + testIndex + '_question';
            wp.editor.initialize(newQuestionEditorId, { tinymce: { wpautop: true }, quicktags: true });

            var newExplanationEditorId = 'mtp_tests_' + testIndex + '_explanation';
            wp.editor.initialize(newExplanationEditorId, { tinymce: { wpautop: true }, quicktags: true });

            testIndex++;
        });

        testsContainer.on('click', '.mtp-remove-test', function() {
            var test = $(this).closest('.mtp-test');
            var editors = test.find('.mtp-test-editor-area');
            editors.each(function() {
                wp.editor.remove($(this).attr('id'));
            });
            test.remove();
        });
    }
});
