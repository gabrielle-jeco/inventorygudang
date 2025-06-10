$(document).ready(function() {
    // Add animation classes to cards
    $('.card').addClass('animate-fade-in');

    // Animate sidebar menu items on hover
    $('.sidebar-menu li').hover(
        function() {
            $(this).find('.nav-link').addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).find('.nav-link').removeClass('animate__animated animate__pulse');
        }
    );

    // Animate buttons on hover
    $('.btn').hover(
        function() {
            $(this).addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).removeClass('animate__animated animate__pulse');
        }
    );

    // Add smooth scrolling to page
    $('a[href*="#"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate(
            {
                scrollTop: $($(this).attr('href')).offset().top,
            },
            500,
            'linear'
        );
    });

    // Add loading animation
    $(document).on('click', 'form button[type="submit"]', function() {
        $(this).addClass('animate__animated animate__pulse');
    });

    // Animate dropdown menus
    $('.dropdown').on('show.bs.dropdown', function() {
        $(this).find('.dropdown-menu').first().addClass('animate__animated animate__fadeIn');
    });

    // Add hover effect to table rows
    $('table tbody tr').hover(
        function() {
            $(this).addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).removeClass('animate__animated animate__pulse');
        }
    );

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Add canvas background animation
    const canvas = document.createElement('canvas');
    canvas.id = 'background-canvas';
    document.body.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const particles = [];
    const particleCount = 50;

    // Create particles
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 2,
            speedX: Math.random() * 2 - 1,
            speedY: Math.random() * 2 - 1
        });
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(particle => {
            particle.x += particle.speedX;
            particle.y += particle.speedY;

            if (particle.x < 0 || particle.x > canvas.width) particle.speedX *= -1;
            if (particle.y < 0 || particle.y > canvas.height) particle.speedY *= -1;

            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(103, 119, 239, 0.1)';
            ctx.fill();
        });

        requestAnimationFrame(animate);
    }

    animate();
}); 