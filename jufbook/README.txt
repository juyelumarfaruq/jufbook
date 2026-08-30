/juf-platform
│
├── /admin                  # Secure admin panel
│   ├── index.php           # Admin Dashboard
│   ├── books.php           # CRUD for books
│   ├── chapters.php        # CRUD & Rich Text Editor for chapters
│   ├── authors.php         # Author management
│   ├── settings.php        # Feature toggles
│   └── /includes           # Admin-specific components (header, footer, auth)
│
├── /assets                 # Static public assets
│   ├── /css                
│   │   ├── style.css       # Main frontend styles (variables, typography)
│   │   └── admin.css       # Admin panel styles
│   ├── /js                 
│   │   ├── main.js         # Core frontend logic (animations, swipe slider)
│   │   ├── ajax.js         # Fetch API logic for pagination/search/subscribe
│   │   └── player.js       # Audio player logic
│   └── /images             # Logos, default UI icons, vector templates
│
├── /config                 # Configuration & bootstrap
│   ├── database.php        # PDO Database connection class
│   └── constants.php       # Site-wide constants (URL, App Name)
│
├── /includes               # Reusable frontend components
│   ├── header.php          # Navbar & SEO Meta tags
│   ├── footer.php          # Footer & Subscribe form
│   ├── functions.php       # Helper functions (dynamic cover generator, ratings)
│   ├── auth.php            # User session validation
│   └── security.php        # CSRF generation and XSS sanitization
│
├── index.php               # Homepage (Hero, Books, Authors, Testimonials)
├── details.php             # Book details & Accordion chapter list
├── read.php                # Zen Mode reading interface
├── search.php              # Dynamic search results
├── login.php               # User login/registration
├── profile.php             # User dashboard (Reading history, favorites)
└── sitemap.php             # Dynamic XML sitemap generator