<?php
// index.php
require_once __DIR__ . '/config/db.php';

try {
    $stmtSettings = $pdo->query("SELECT * FROM portfolio_settings LIMIT 1");
    $settings = $stmtSettings->fetch();

    $stmtProjects = $pdo->query("SELECT * FROM projects WHERE is_visible = TRUE ORDER BY created_at DESC");
    $projects = $stmtProjects->fetchAll();

    $stmtCert = $pdo->query("SELECT * FROM certifications ORDER BY display_order ASC, created_at DESC");
    $certifications = $stmtCert->fetchAll();
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['hero_title'] ?? 'Nicolás Fernández | Dev') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <img src="spiderman 1.gif" id="hanging-spidey" alt="Spiderman Colgando" class="hanging-spidey">
    <div id="spider-sense-overlay"></div>

    <header class="navbar">
        <div class="container nav-wrapper">
            <a href="#" class="logo" id="spidey-btn">
                NF<span>.dev</span>
                <i class="ph-fill ph-spider spider-icon"></i>
            </a>
            <nav>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#proyectos">Proyectos</a></li>
                    <li><a href="#credenciales">Credenciales</a></li>
                    <li><a href="#skills">Habilidades</a></li>
                </ul>
            </nav>
            <?php if (!empty($settings['cv_url'])): ?>
                <a href="<?= htmlspecialchars(getStorageUrl($settings['cv_url'])) ?>" target="_blank" class="btn-terminal">
                    <i class="ph ph-file-pdf"></i> Descargar CV
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        
        <section id="inicio" class="hero-section">
            <div class="hero-grid">
                <div class="hero-main-box">
                    <div class="box-header">
                        <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                        <span class="box-title">Perfil Profesional</span>
                    </div>
                    <div class="box-content">
                        <span class="status-indicator">● Abierto a oportunidades</span>
                        <h1><?= htmlspecialchars($settings['hero_title'] ?? 'Nicolás Fernández') ?></h1>
                        <h2><?= htmlspecialchars($settings['hero_subtitle'] ?? 'Ingeniero Informático / Desarrollo y Soporte TI') ?></h2>
                        <p class="bio-text">
                            <?= htmlspecialchars($settings['hero_description'] ?? '') ?>
                            </p>
                        <div class="action-buttons">
                            <a href="#proyectos" class="btn-brutal-primary">Ver proyectos</a>
                            <button id="contact-btn" class="btn-brutal-secondary" style="cursor: pointer;">Contactar</button>
                        </div>
                    </div>
                </div>
        </section>

        <section id="acerca" class="section-block">
            <h3 class="block-heading"><i class="ph-bold ph-info"></i> Acerca de Mí</h3>
            <div class="about-box">
                <div class="about-content">
                    <p><?= htmlspecialchars($settings['about_text']) ?></p>
                </div>
                <?php if (!empty($settings['profile_image_url'])): ?>
                    <div class="about-avatar">
                        <img src="<?= getStorageUrl($settings['profile_image_url']) ?>" alt="Nicolás Fernández">
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section id="skills" class="section-block">
            <h3 class="block-heading">
                <i class="ph-bold ph-code"></i> Habilidades & Tecnologías
            </h3>

            <div class="skills-grid">

                <div class="skill-box">
                    <div class="box-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="box-title">Frontend</span>
                    </div>

                    <div class="skills-content">

                        <?php
                        $frontend = !empty($settings['skills_frontend'])
                            ? str_getcsv(trim($settings['skills_frontend'], '{}'))
                            : [];
                        ?>

                        <?php foreach ($frontend as $skill): ?>
                            <span class="skill-pill">
                                <?= htmlspecialchars(trim($skill)) ?>
                            </span>
                        <?php endforeach; ?>

                    </div>
                </div>

                <div class="skill-box">

                    <div class="box-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="box-title">Backend</span>
                    </div>

                    <div class="skills-content">

                        <?php
                        $backend = !empty($settings['skills_backend'])
                            ? str_getcsv(trim($settings['skills_backend'], '{}'))
                            : [];
                        ?>

                        <?php foreach ($backend as $skill): ?>
                            <span class="skill-pill">
                                <?= htmlspecialchars(trim($skill)) ?>
                            </span>
                        <?php endforeach; ?>

                    </div>

                </div>

                <div class="skill-box">

                    <div class="box-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="box-title">Herramientas</span>
                    </div>

                    <div class="skills-content">

                        <?php
                        $tools = !empty($settings['skills_tools'])
                            ? str_getcsv(trim($settings['skills_tools'], '{}'))
                            : [];
                        ?>

                        <?php foreach ($tools as $skill): ?>
                            <span class="skill-pill">
                                <?= htmlspecialchars(trim($skill)) ?>
                            </span>
                        <?php endforeach; ?>

                    </div>

                </div>

            </div>
        </section>

        <section id="proyectos" class="section-block">
            <h3 class="block-heading"><i class="ph-bold ph-folder-open"></i> Proyectos Destacados y Experiencia Profesional</h3>
            <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
                <article class="brutal-card">
                    <?php 
                    $imageUrl = null;
                    $cleanImages = [];
                    if (!empty($project['image_urls'])):
                        $images = $project['image_urls'];
                        if (is_string($images)) {
                            // Intentar JSON primero (JSONB), si falla parsear como array PostgreSQL text[] → {"url1","url2"}
                            $decoded = json_decode($images, true);
                            if (is_array($decoded)) {
                                $images = $decoded;
                            } else {
                                $images = str_getcsv(trim($images, '{}'));
                            }
                        }
                        // Limpiar comillas residuales y guardar
                        if (is_array($images) && !empty($images)) {
                            foreach ($images as $img) {
                                $cleanImages[] = trim($img, '"');
                            }
                            $imageUrl = $cleanImages[0] ?? null;
                        }
                    endif;
                    
                    if (!empty($imageUrl)): ?>
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                    <?php endif; ?>
                    <div class="card-top"><i class="ph-bold ph-code-block font-icon"></i><span class="card-id">Proyecto</span></div>
                    <div class="card-body">
                        <h4><?= htmlspecialchars($project['title']) ?></h4>
                        <?php 
                        $fullDesc = $project['description'] ?? '';
                        $projectTitle = $project['title'] ?? '';
                        
                        // Resúmenes en duro personalizados para cada proyecto
                        $customSummaries = [
                            'ArteCom' => 'Comunidad digital para publicar y vender arte.',
                            'AgroLink' => 'Conexión directa entre agricultores y compradores.',
                            'Sistema ERP DIMAK' => 'Sistema web ERP con 3 módulos.'
                        ];
                        
                        $shortDesc = $customSummaries[$projectTitle] ?? ((mb_strlen($fullDesc) > 120) ? mb_substr($fullDesc, 0, 120) . '...' : $fullDesc);
                        ?>
                        <p class="desc-clamp"><?= htmlspecialchars($shortDesc) ?></p>
                        <button class="btn-read-more trigger-modal" data-title="<?= htmlspecialchars($project['title'], ENT_QUOTES) ?>" data-desc="<?= htmlspecialchars($fullDesc, ENT_QUOTES) ?>" data-images="<?= htmlspecialchars(json_encode($cleanImages), ENT_QUOTES) ?>">Ver más</button>
                        <?php 
                        $tags = !empty($project['tags']) ? str_getcsv(trim($project['tags'], '{}')) : [];
                        if (!empty($tags)): ?>
                            <div class="tech-tags">
                                <?php foreach ($tags as $tag): ?><span class="tech-badge">#<?= htmlspecialchars(trim($tag)) ?></span><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="card-links">
                            <?php if (!empty($project['github_url'])): ?><a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" class="link-btn"><i class="ph-bold ph-github-logo"></i> Código Fuente</a><?php endif; ?>
                            <?php if (!empty($project['live_url'])): ?><a href="<?= htmlspecialchars($project['live_url']) ?>" target="_blank" class="link-btn live"><i class="ph-bold ph-broadcast"></i> Sitio Web</a><?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            </div>
        </section>

        <section id="credenciales" class="section-block">
            <h3 class="block-heading"><i class="ph-bold ph-certificate"></i> Certificaciones</h3>
            <div class="cert-matrix">
                <?php foreach ($certifications as $cert): ?>
                    <div class="matrix-row">
                        <div class="matrix-cell icon-cell"><i class="ph-bold ph-shield-check"></i></div>
                        <div class="matrix-cell main-cell"><h5><?= htmlspecialchars($cert['name']) ?></h5><span class="sub-text"><?= htmlspecialchars($cert['institution']) ?></span></div>
                        <div class="matrix-cell date-cell"><span>[<?= htmlspecialchars($cert['issued_date']) ?>]</span></div>
                        <div class="matrix-cell action-cell"><a href="<?= htmlspecialchars(getStorageUrl($cert['file_url'])) ?>" target="_blank" class="matrix-link">Ver<i class="ph ph-arrow-square-out"></i></a></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="terminal-footer">
        <div class="container footer-content" style="position: relative;">
            <div><p>© <?= date('Y') ?> Nicolás Fernández</p></div>
        </div>
    </footer>

    <div id="project-modal" class="brutal-modal-overlay">
        <div class="brutal-modal">
            <div class="modal-header"><span class="modal-title" id="modal-title-display"></span><button id="close-modal" class="close-btn">[X]</button></div>
            <div class="modal-body">
                <div id="modal-carousel" class="modal-carousel-container"></div>
                <h3 id="modal-project-title"></h3>
                <div id="modal-project-desc" class="modal-desc-full"></div>
            </div>
        </div>
    </div>

    <!-- Lightbox / Fullscreen Modal -->
    <div id="lightbox-modal" class="brutal-modal-overlay lightbox-overlay">
        <button id="close-lightbox" class="close-btn lightbox-close-btn">[X] Cerrar</button>
        <button id="lightbox-prev" class="lightbox-btn prev">&lt;</button>
        <div class="lightbox-content">
            <img id="lightbox-image" src="" alt="Vista ampliada">
        </div>
        <button id="lightbox-next" class="lightbox-btn next">&gt;</button>
    </div>

    <!-- Contact Modal -->
    <div id="contact-modal" class="brutal-modal-overlay">
        <div class="brutal-modal contact-modal-box">
            <div class="modal-header">
                <span class="modal-title">Contacto</span>
                <button id="close-contact-modal" class="close-btn">[X]</button>
            </div>
            <div class="modal-body contact-modal-body">
                <h3>¿Cómo prefieres contactarme?</h3>
                <div class="contact-options">
                    <a href="mailto:<?= htmlspecialchars($settings['email'] ?? '') ?>" class="btn-contact-option email-option">
                        <i class="ph-bold ph-envelope"></i> Correo Electrónico
                    </a>
                    <a href="https://wa.me/56956115492" target="_blank" class="btn-contact-option whatsapp-option">
                        <i class="ph-bold ph-whatsapp-logo"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>



    <script>
        document.getElementById('spidey-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const overlay = document.getElementById('spider-sense-overlay');
            const hangingSpidey = document.getElementById('hanging-spidey');
            overlay.classList.add('active'); document.body.classList.add('shake-screen');
            hangingSpidey.classList.add('drop');
            setTimeout(() => { overlay.classList.remove('active'); document.body.classList.remove('shake-screen'); }, 500);
            setTimeout(() => { hangingSpidey.classList.remove('drop'); }, 10000); 
        });

        const modal = document.getElementById('project-modal');
        const btnClose = document.getElementById('close-modal');
        const modalCarousel = document.getElementById('modal-carousel');
        
        let currentSlide = 0;

        function showSlide(index) {
            const slides = modalCarousel.querySelectorAll('.carousel-slide-wrapper');
            const indicators = modalCarousel.querySelectorAll('.carousel-indicator');
            if (slides.length === 0) return;

            if (index >= slides.length) currentSlide = 0;
            else if (index < 0) currentSlide = slides.length - 1;
            else currentSlide = index;

            slides.forEach((slide, i) => {
                slide.style.display = i === currentSlide ? 'flex' : 'none';
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === currentSlide);
            });
        }

        document.querySelectorAll('.trigger-modal').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('modal-project-title').textContent = button.getAttribute('data-title');
                document.getElementById('modal-project-desc').textContent = button.getAttribute('data-desc');
                document.getElementById('modal-title-display').textContent = `cat ${button.getAttribute('data-title').toLowerCase().replace(/\s+/g, '_')}.log`;
                
                // Cargar carrusel de imágenes
                const imagesData = button.getAttribute('data-images');
                let images = [];
                try {
                    images = JSON.parse(imagesData) || [];
                } catch(e) {
                    images = [];
                }

                modalCarousel.innerHTML = '';
                if (images.length > 0) {
                    modalCarousel.classList.add('active');
                    
                    // Crear slides
                    images.forEach((imgUrl, idx) => {
                        const slide = document.createElement('div');
                        slide.className = 'carousel-slide-wrapper';
                        slide.style.display = idx === 0 ? 'flex' : 'none';
                        
                        const img = document.createElement('img');
                        img.src = imgUrl;
                        img.alt = `Project Image ${idx + 1}`;
                        img.className = 'carousel-image';
                        img.style.cursor = 'zoom-in';
                        
                        // Clic en la imagen para abrir a pantalla completa (Lightbox)
                        img.addEventListener('click', () => {
                            openLightbox(idx, images);
                        });
                        
                        slide.appendChild(img);
                        modalCarousel.appendChild(slide);
                    });

                    // Si hay más de una imagen, agregar botones de navegación e indicadores
                    if (images.length > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.className = 'carousel-btn prev';
                        prevBtn.textContent = '<';
                        prevBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            showSlide(currentSlide - 1);
                        });
                        
                        const nextBtn = document.createElement('button');
                        nextBtn.className = 'carousel-btn next';
                        nextBtn.textContent = '>';
                        nextBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            showSlide(currentSlide + 1);
                        });

                        const indicatorsContainer = document.createElement('div');
                        indicatorsContainer.className = 'carousel-indicators';
                        images.forEach((_, idx) => {
                            const indicator = document.createElement('span');
                            indicator.className = `carousel-indicator ${idx === 0 ? 'active' : ''}`;
                            indicator.addEventListener('click', (e) => {
                                e.stopPropagation();
                                showSlide(idx);
                            });
                            indicatorsContainer.appendChild(indicator);
                        });

                        modalCarousel.appendChild(prevBtn);
                        modalCarousel.appendChild(nextBtn);
                        modalCarousel.appendChild(indicatorsContainer);
                    }
                    currentSlide = 0;
                } else {
                    modalCarousel.classList.remove('active');
                }

                modal.classList.add('active');
            });
        });

        // Lógica del Lightbox (Pantalla Completa)
        const lightbox = document.getElementById('lightbox-modal');
        const lightboxImg = document.getElementById('lightbox-image');
        const btnCloseLightbox = document.getElementById('close-lightbox');
        const btnLightboxPrev = document.getElementById('lightbox-prev');
        const btnLightboxNext = document.getElementById('lightbox-next');
        
        let lightboxIndex = 0;
        let lightboxImages = [];

        function showLightboxImage(index) {
            if (lightboxImages.length === 0) return;
            if (index >= lightboxImages.length) lightboxIndex = 0;
            else if (index < 0) lightboxIndex = lightboxImages.length - 1;
            else lightboxIndex = index;

            lightboxImg.src = lightboxImages[lightboxIndex];
            
            // Sincronizar el carrusel del fondo
            showSlide(lightboxIndex);
        }

        function openLightbox(index, images) {
            lightboxImages = images;
            showLightboxImage(index);
            lightbox.classList.add('active');
            
            if (images.length > 1) {
                btnLightboxPrev.style.display = 'block';
                btnLightboxNext.style.display = 'block';
            } else {
                btnLightboxPrev.style.display = 'none';
                btnLightboxNext.style.display = 'none';
            }
        }

        const closeModal = () => modal.classList.remove('active');
        const closeLightbox = () => lightbox.classList.remove('active');

        // Lógica del Modal de Contacto
        const contactModal = document.getElementById('contact-modal');
        const contactBtn = document.getElementById('contact-btn');
        const closeContactBtn = document.getElementById('close-contact-modal');
        const closeContact = () => contactModal.classList.remove('active');

        if (contactBtn) {
            contactBtn.addEventListener('click', (e) => {
                e.preventDefault();
                contactModal.classList.add('active');
            });
        }

        if (closeContactBtn) closeContactBtn.addEventListener('click', closeContact);
        contactModal.addEventListener('click', (e) => {
            if (e.target === contactModal) closeContact();
        });

        if(btnClose) btnClose.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if(e.target === modal) closeModal(); });
        
        if (btnCloseLightbox) btnCloseLightbox.addEventListener('click', closeLightbox);
        if (btnLightboxPrev) {
            btnLightboxPrev.addEventListener('click', (e) => {
                e.stopPropagation();
                showLightboxImage(lightboxIndex - 1);
            });
        }
        if (btnLightboxNext) {
            btnLightboxNext.addEventListener('click', (e) => {
                e.stopPropagation();
                showLightboxImage(lightboxIndex + 1);
            });
        }
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox || e.target.classList.contains('lightbox-content')) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (e) => {
            if(e.key === 'Escape') {
                closeModal();
                closeLightbox();
                closeContact();
            }
        });
    </script>
</body>
</html>