$(document).ready(function() {
    // Show/hide 'Other' service field
    $('#serviceType').on('change', function() {
        if ($(this).val() === 'Other') {
            $('#otherService').removeClass('d-none').attr('required', true);
        } else {
            $('#otherService').addClass('d-none').val('').removeAttr('required');
        }
    });

    // Smooth scroll for nav links
    $('.nav-link').on('click', function(e) {
        var target = $(this).attr('href');
        if (target.startsWith('#')) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: $(target).offset().top - 70 }, 600);
        }
    });

    // Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Progress bar for request form
    function updateProgressBar(percent) {
        $('#formProgressBar').show();
        $('#formProgressBar .progress-bar').css('width', percent + '%');
        if (percent >= 100) {
            setTimeout(function() { $('#formProgressBar').hide(); }, 800);
        }
    }

    // jQuery Validate for the form
    $('#serviceRequestForm').validate({
        rules: {
            fullName: 'required',
            email: {
                required: true,
                email: true
            },
            phone: 'required',
            serviceType: 'required',
            numGuards: {
                required: true,
                min: 1
            },
            message: 'required',
            otherService: {
                required: function(element) {
                    return $('#serviceType').val() === 'Other';
                }
            }
        },
        messages: {
            fullName: 'Please enter your full name',
            email: 'Please enter a valid email address',
            phone: 'Please enter your phone number',
            serviceType: 'Please select a service type',
            numGuards: 'Please specify the number of guards needed',
            message: 'Please enter your message',
            otherService: 'Please specify the service'
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        submitHandler: function(form) {
            updateProgressBar(30);
            // AJAX submit
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                beforeSend: function() { updateProgressBar(60); },
                success: function(response) {
                    updateProgressBar(100);
                    if (response.success) {
                        $('#form-messages').html('<div class="alert alert-success">' + response.message + '</div>');
                        form.reset();
                        $(form).find('.is-valid').removeClass('is-valid');
                        setTimeout(function() {
                            $('#requestServiceModal').modal('hide');
                        }, 2000);
                        // Notify dashboard
                        localStorage.setItem('newRequest', '1');
                    } else {
                        $('#form-messages').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    updateProgressBar(100);
                    $('#form-messages').html('<div class="alert alert-danger">An error occurred. Please try again later.</div>');
                }
            });
            return false;
        }
    });

    // Floating Security Alert Button
    $('#openAlertModal').on('click', function() {
        $('#securityAlertModal').modal('show');
    });

    // Security Alert Modal: Location Sharing
    $('#getLocationBtn').on('click', function() {
        var $btn = $(this);
        var $status = $('#locationStatus');
        $btn.prop('disabled', true);
        $status.text('Getting location...');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var coords = position.coords.latitude + ',' + position.coords.longitude;
                $('#alertLocation').val(coords);
                $status.text('Location attached!');
                $btn.text('Location Attached').removeClass('btn-outline-primary').addClass('btn-success');
            }, function() {
                $status.text('Unable to get location.');
                $btn.prop('disabled', false);
            });
        } else {
            $status.text('Geolocation not supported.');
            $btn.prop('disabled', false);
        }
    });

    // Security Alert Form Submission (AJAX)
    $('#securityAlertForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $msg = $('#alert-form-messages');
        var alertMsg = $('#alertMessage').val().trim();
        if (!alertMsg) {
            $msg.html('<div class="alert alert-danger">Please describe your emergency.</div>');
            return;
        }
        $msg.html('<div class="alert alert-info">Sending alert...</div>');
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $msg.html('<div class="alert alert-success">' + response.message + '</div>');
                    $form[0].reset();
                    setTimeout(function() {
                        $('#securityAlertModal').modal('hide');
                        $msg.html('');
                    }, 2000);
                    // Notify dashboard
                    localStorage.setItem('newAlert', '1');
                } else {
                    $msg.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $msg.html('<div class="alert alert-danger">An error occurred. Please try again later.</div>');
            }
        });
    });

    // Newsletter subscription AJAX
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $msg = $('#newsletterMsg');
        $msg.text('Subscribing...');
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                $msg.text(response.message);
                if (response.success) {
                    $form[0].reset();
                }
            },
            error: function() {
                $msg.text('An error occurred.');
            }
        });
    });

    // Staff login AJAX
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $msg = $('#login-messages');
        $msg.text('Logging in...');
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $msg.html('<div class="alert alert-success">' + response.message + '</div>');
                    localStorage.setItem('staffLoggedIn', '1');
                    $('#loginModal').modal('hide');
                    $('#dashboardNav').removeClass('d-none');
                    $('#loginNav').addClass('d-none');
                    window.location.href = 'dashboard.html';
                } else {
                    $msg.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $msg.html('<div class="alert alert-danger">An error occurred. Please try again.');
            }
        });
    });

    // Show/hide dashboard link based on login
    if (localStorage.getItem('staffLoggedIn')) {
        $('#dashboardNav').removeClass('d-none');
        $('#loginNav').addClass('d-none');
    } else {
        $('#dashboardNav').addClass('d-none');
        $('#loginNav').removeClass('d-none');
    }

    // Staff popups for new alerts/requests (simulate real-time)
    window.addEventListener('storage', function(e) {
        if (localStorage.getItem('staffLoggedIn')) {
            if (e.key === 'newAlert' && e.newValue === '1') {
                showPopup('New emergency alert received!');
                localStorage.setItem('newAlert', '0');
            }
            if (e.key === 'newRequest' && e.newValue === '1') {
                showPopup('New service request received!');
                localStorage.setItem('newRequest', '0');
            }
        }
    });
    function showPopup(msg) {
        var $popup = $('<div class="alert alert-info" style="position:fixed;top:20px;right:20px;z-index:3000;">'+msg+'</div>');
        $('body').append($popup);
        setTimeout(function() { $popup.fadeOut(400, function(){ $popup.remove(); }); }, 4000);
    }
}); 