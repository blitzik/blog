module.exports = function (grunt) {

    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            mutual_js: {
                options: {
                    separator: ';'
                },

                src: [
                    'bower_components/jquery/dist/jquery.js',
                    'bower_components/nette-forms/src/assets/netteForms.js',
                    'bower_components/nette.ajax.js/nette.ajax.js',
                    'bower_components/nette.ajax.js/extensions/spinner.ajax.js',
                    //'bower_components/bootstrap-sass/assets/javascripts/bootstrap.js',
                    //'assets/js/my_js/image_management.js',
                    //'assets/js/my_js/webfont.js',
                    'assets/js/my_js/main.js'
                ],
                dest: 'assets/js/original/js.js'
            },

            edit_page: {
                options: {
                    separator: ';'
                },

                src: [
                    'assets/js/original/jquery.datetimepicker.js',
                    'assets/js/my_js/editPageDatetimepicker.js',
                    'assets/js/my_js/editPage.js',
                    'assets/js/my_js/tagsPicking.js',
                    'assets/js/original/autosize.js'
                ],
                dest: 'assets/js/original/editPage.js'
            },

            comments: {
                options: {
                    separator: ';'
                },

                src: [
                    'assets/js/original/autosize.js',
                    'assets/js/my_js/comments.js'
                ],
                dest: 'assets/js/original/comments.js'
            }
        },

        uglify: {
            mutual: {
                files: {
                    'assets/js/js.min.js': 'assets/js/original/js.js'
                }
            },

            edit_page: {
                files: {
                    'assets/js/editPage.min.js': 'assets/js/original/editPage.js'
                }
            },

            comments: {
                files: {
                    'assets/js/comments.js': 'assets/js/original/comments.js'
                }
            },

            tags_picking: {
                files: {
                    'assets/js/tagsPicking.min.js': 'assets/js/my_js/tagsPicking.js'
                }
            }
        },

        cssmin: {
            front: {
                files: {
                    'assets/css/front.min.css': 'assets/css/original/front.css'
                }
            },

            admin: {
                files: {
                    'assets/css/admin.min.css': 'assets/css/original/admin.css'
                }
            },

            datetime_picker: {
                files: {
                    'assets/css/jquery.datetimepicker.min.css': 'assets/css/original/jquery.datetimepicker.css'
                }
            }
        },

        sass: {
            front: {
                files: {
                    'assets/css/original/front.css': [
                        'assets/css/SCSS/front/front.scss'
                    ]
                }
            },
            admin: {
                files: {
                    'assets/css/original/admin.css': 'assets/css/SCSS/admin/admin.scss'
                }
            }
        },

        watch: {
            front: {
                files: [
                    'assets/css/SCSS/_grid.scss',
                    'assets/css/SCSS/_common.scss',
                    'assets/css/SCSS/_paginator.scss',
                    'assets/css/SCSS/front/_blog_front.scss',
                    'assets/css/SCSS/front/front.scss'
                ],
                tasks: ['sass:front', 'cssmin:front']
            },

            admin: {
                files: [
                    'assets/css/SCSS/_grid.scss',
                    'assets/css/SCSS/_common.scss',
                    'assets/css/SCSS/admin/_my-variables.scss',
                    'assets/css/SCSS/admin/_blog_admin.scss',
                    'assets/css/SCSS/admin/admin.scss'
                ],
                tasks: ['sass:admin', 'cssmin:admin']
            }
        },

        copy: {
            paginator: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['libs/visual_paginator/_paginator.scss'],
                        dest: 'assets/css/SCSS/'
                    }
                ]
            },

            font_awesome: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/font-awesome-sass/assets/fonts/font-awesome/*'],
                        dest: 'assets/fonts/font-awesome/'
                    }
                ]
            },

            datetime_picker: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/datetimepicker/jquery.datetimepicker.css'],
                        dest: 'assets/css/original/'
                    },
                    {
                        expand:true,
                        flatten: true,
                        src: ['bower_components/datetimepicker/jquery.datetimepicker.js'],
                        dest: 'assets/js/original/'
                    }
                ]
            },

            jquery_autosize: {
                files: [
                    {
                        expand: true,
                        flatten: true,
                        src: ['bower_components/autosize/dist/autosize.js'],
                        dest: 'assets/js/original/'
                    }
                ]
            }
        }

    });

    grunt.registerTask('default', ['copy', 'sass', 'concat', 'cssmin', 'uglify']);

    grunt.registerTask('build_front_css', ['sass:front', 'cssmin:front']);
    grunt.registerTask('build_admin_css', ['sass:admin', 'cssmin:admin']);

    grunt.registerTask('build_js', ['concat:mutual_js', 'uglify:mutual']);

    grunt.registerTask('build_comments_js', ['concat:comments', 'uglify:comments']);

    grunt.registerTask('build_admin_css_js', ['sass:admin', 'cssmin:admin', 'cssmin:datetime_picker', 'concat:mutual_js', 'concat:edit_page', 'uglify:mutual', 'uglify:edit_page']);
    grunt.registerTask('build_admin_page_js', ['concat:edit_page', 'uglify:edit_page']);

    grunt.registerTask('watch_front_css', ['watch:front']);
    grunt.registerTask('watch_admin_css', ['watch:admin']);
};