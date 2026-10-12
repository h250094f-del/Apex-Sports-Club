/**
 * Apex Sports Club — Main JavaScript Module
 * ISE 2102: Web Technologies
 * 
 * Demonstrates:
 * - Arrays and Objects (data models)
 * - Events (click, submit, input, keydown, resize)
 * - DOM manipulation & filtering
 * - Form validation (client-side)
 * - Interactive Lightbox Modal
 */

// ==========================================
// 1. DATA MODELS (Arrays & Objects)
// ==========================================

const SPORTS_DATA = [
  {
    id: "football",
    name: "Football",
    tagline: "Play together. Compete together.",
    description: "Weekly friendly matches, tactical training, and campus league tournaments for all skill levels.",
    image: "assets/football.jpg",
    badge: "Most Popular",
    category: "Team Sports"
  },
  {
    id: "basketball",
    name: "Basketball",
    tagline: "Find your rhythm on the court.",
    description: "Fast-paced pick-up games, shooting drills, and 3v3 weekend showdowns on indoor and outdoor courts.",
    image: "assets/basketball.jpg",
    badge: "Fast Paced",
    category: "Court Sports"
  },
  {
    id: "fitness",
    name: "Gym & Fitness",
    tagline: "Build strength one session at a time.",
    description: "Structured conditioning circuits, functional strength workshops, and high-energy group fitness classes.",
    image: "assets/girl_holding_ropes.jpeg",
    badge: "All Levels",
    category: "Conditioning"
  },
  {
    id: "volleyball",
    name: "Volleyball",
    tagline: "Teamwork above the net.",
    description: "Spike, set, and serve in mixed recreational games and faculty challenge matches on our sand and indoor courts.",
    image: "assets/volley.jpeg",
    badge: "Social & Fun",
    category: "Court Sports"
  },
  {
    id: "running",
    name: "Running & Athletics",
    tagline: "Set a goal. Find your pace.",
    description: "Weekly sunrise distance runs, interval track training, 5K campus challenges, and sprint technique sessions.",
    image: "assets/sports.jpeg",
    badge: "Cardio & Stamina",
    category: "Endurance"
  },
  {
    id: "social",
    name: "Social Sports",
    tagline: "Move, connect and unwind.",
    description: "Casual multi-sport tournaments, sports trivia, outdoor hikes, and interfaculty fun days.",
    image: "assets/pic_of_balls.jpeg",
    badge: "Community",
    category: "Social"
  }
];

const EVENTS_DATA = [
  {
    id: 1,
    title: "Community Football Match",
    sport: "Football",
    date: "Saturday, 24 Nov",
    time: "10:00 AM - 12:30 PM",
    venue: "Campus Football Pitch ",
    level: "All Abilities Welcome",
    description: "Bring your boots for an open 7v7 friendly match. Great way to meet members and warm up for the semester league."
  },
  {
    id: 2,
    title: "3v3 Basketball Tournament",
    sport: "Basketball",
    date: "Wednesday, 28 Oct",
    time: "4:30 PM - 7:00 PM",
    venue: "Campus Basketball Court",
    level: "Intermediate & Advanced",
    description: "Fast half-court matches with running clocks. Enter as a squad of three or join the free-agent pool on arrival."
  },
  {
    id: 3,
    title: "Sunset 5K Campus Run",
    sport: "Running",
    date: "Friday, 30 Oct",
    time: "5:30 PM - 6:45 PM",
    venue: "Campus Main Gate (Start / Finish)",
    level: "All Paces (Walkers to Sprinters)",
    description: "A scenic loop around campus perimeter paths led by student pacers. Free hydration and fruit at the finish line."
  },
  {
    id: 4,
    title: "Functional Strength & Core Circuit",
    sport: "Fitness",
    date: "Monday, 2 Nov",
    time: "5:00 PM - 6:15 PM",
    venue: "H2 Gym",
    level: "Beginner Friendly",
    description: "Learn safe lifting techniques, kettlebell flows, and bodyweight core routines guided by peer fitness coaches."
  },
  {
    id: 5,
    title: "Volleyball Spike & Serve Clinic",
    sport: "Volleyball",
    date: "Thursday, 5 Nov",
    time: "4:00 PM - 6:00 PM",
    venue: "Volleyball Court ",
    level: "Open to Everyone",
    description: "Sharpen your jump serves, setting consistency, and defensive positioning with fun structured scrimmages."
  }
];

const COMMITTEE_DATA = [
  {
    name: "Gary Munamba",
    role: "President",
    dept: "Software Engineering",
    bio: "Passionate about building community through grassroots university sport."
  },
  {
    name: "Craig Mundoza",
    role: "Pharmacy",
    dept: "Faculty of Health Sciences",
    bio: "Advocating for health equity, female participation, and wellness habits on campus."
  },
  {
    name: "Valerie Ngudu",
    role: "Sports Coordinator",
    dept: "Financial Engineering",
    bio: "Organises weekly fixtures, officiating, pitch reservations, and tournament brackets."
  },
  {
    name: "Tazvi Mhaka",
    role: "Community & Media Lead",
    dept: "Chemical Engineering",
    bio: "Manages social channels, club branding, member communications, and student events."
  }
];

const GALLERY_DATA = [
  {
    id: 1,
    title: "Football Match",
    category: "Football",
    desc: "A football match on the pitch.",
    image: "assets/football.jpg"
  },
  {
    id: 2,
    title: "Basketball Game",
    category: "Basketball",
    desc: "Players competing during a basketball game.",
    image: "assets/basketball.jpg"
  },
  {
    id: 3,
    title: "Battle Ropes Conditioning",
    category: "Fitness",
    desc: "A strength and conditioning workout with battle ropes.",
    image: "assets/girl_holding_ropes.jpeg"
  },
  {
    id: 4,
    title: "Beach Volleyball",
    category: "Volleyball",
    desc: "Volleyball players competing at sunset.",
    image: "assets/volley.jpeg"
  },
  {
    id: 5,
    title: "Sports in Action",
    category: "Sports",
    desc: "A collection of activities from across the sporting community.",
    image: "assets/sports.jpeg"
  },
  {
    id: 6,
    title: "Sports Equipment",
    category: "Sports",
    desc: "Equipment for a range of sports and activities.",
    image: "assets/pic_of_balls.jpeg"
  }
];

// ==========================================
// 2. DOM INITIALISATION & RENDERING
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
  initCommon();
  renderSportsGrids();
  renderEvents();
  renderCommittee();
  renderGallery();
  initFormValidation();
  checkUrlParams();
});

/**
 * Common site utilities (Year, Mobile Menu)
 */
function initCommon() {
  // Dynamic footer year
  const yearEl = document.getElementById("year");
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  // Mobile menu toggle
  const burger = document.getElementById("burger");
  const mobileNav = document.getElementById("mobile-nav");
  if (burger && mobileNav) {
    burger.addEventListener("click", () => {
      const isOpen = burger.classList.toggle("open");
      mobileNav.classList.toggle("open");
      burger.setAttribute("aria-expanded", isOpen ? "true" : "false");
      mobileNav.setAttribute("aria-hidden", isOpen ? "false" : "true");
    });

    // Close on escape
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && burger.classList.contains("open")) {
        burger.classList.remove("open");
        mobileNav.classList.remove("open");
        burger.setAttribute("aria-expanded", "false");
        mobileNav.setAttribute("aria-hidden", "true");
      }
    });
  }
}

/**
 * Render Sports Cards on Home and Activities pages
 */
function renderSportsGrids() {
  const homeGrid = document.getElementById("sports-grid");
  const actGrid = document.getElementById("activities-sports-grid");

  const markup = SPORTS_DATA.map(sport => `
    <article class="sport-card" id="${sport.id}">
      <div class="sport-card-img">
        <img src="${sport.image}" alt="${sport.name} at Apex Sports Club" loading="lazy" />
        <span class="sport-badge">${sport.badge}</span>
      </div>
      <div class="sport-body">
        <div class="sport-head">
          <h3>${sport.name}</h3>
        </div>
        <div class="sport-tagline">${sport.tagline}</div>
        <p>${sport.description}</p>
        <a href="join.html?sport=${encodeURIComponent(sport.name)}" class="sport-link">
          Join ${sport.name}
        </a>
      </div>
    </article>
  `).join("");

  if (homeGrid) homeGrid.innerHTML = markup;
  if (actGrid) actGrid.innerHTML = markup;
}

/**
 * Render and filter Events
 */
function renderEvents() {
  const homeEvents = document.getElementById("home-events-grid");
  const allEventsGrid = document.getElementById("events-grid");
  const filterBar = document.getElementById("filter-bar");
  const emptyState = document.getElementById("events-empty");

  // Home page: top 3 events
  if (homeEvents) {
    homeEvents.innerHTML = EVENTS_DATA.slice(0, 3).map(ev => createEventCard(ev)).join("");
  }

  // Activities page: full interactive filter
  if (allEventsGrid && filterBar) {
    const categories = ["All", ...new Set(EVENTS_DATA.map(e => e.sport))];
    
    // Render filter buttons
    filterBar.innerHTML = categories.map((cat, idx) => `
      <button class="filter-btn ${idx === 0 ? 'active' : ''}" data-filter="${cat}">
        ${cat}
      </button>
    `).join("");

    // Initial render
    renderFilteredEvents("All");

    // Filter event listener
    filterBar.addEventListener("click", (e) => {
      const btn = e.target.closest(".filter-btn");
      if (!btn) return;

      filterBar.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const selected = btn.getAttribute("data-filter");
      renderFilteredEvents(selected);
    });

    const resetBtn = document.getElementById("reset-events-btn");
    if (resetBtn) {
      resetBtn.addEventListener("click", () => {
        const allBtn = filterBar.querySelector('[data-filter="All"]');
        if (allBtn) allBtn.click();
      });
    }

    function renderFilteredEvents(cat) {
      const filtered = cat === "All" ? EVENTS_DATA : EVENTS_DATA.filter(e => e.sport === cat);
      if (filtered.length === 0) {
        allEventsGrid.innerHTML = "";
        if (emptyState) emptyState.style.display = "block";
      } else {
        if (emptyState) emptyState.style.display = "none";
        allEventsGrid.innerHTML = filtered.map(ev => createEventCard(ev)).join("");
      }
    }
  }

  function createEventCard(ev) {
    return `
      <article class="event-card">
        <div class="event-top">
          <span class="event-badge">${ev.sport}</span>
        </div>
        <h3>${ev.title}</h3>
        <ul class="event-meta">
          <li><strong>Date:</strong><span>${ev.date} &bull; ${ev.time}</span></li>
          <li><strong>Location:</strong><span>${ev.venue}</span></li>
          <li><strong>Level:</strong><span>${ev.level}</span></li>
        </ul>
        <p class="event-desc">${ev.description}</p>
        <a href="join.html?purpose=event&sport=${encodeURIComponent(ev.sport)}" class="btn btn-outline btn-sm">
          Register for Session
        </a>
      </article>
    `;
  }
}

/**
 * Render Committee on About page
 */
function renderCommittee() {
  const grid = document.getElementById("committee-grid");
  if (!grid) return;

  grid.innerHTML = COMMITTEE_DATA.map(member => `
    <article class="committee-card">
      <div class="committee-avatar" aria-hidden="true">${member.name.split(/\s+/).map(part => part.charAt(0)).join("")}</div>
      <div class="committee-role">${member.role}</div>
      <h3>${member.name}</h3>
      <p style="color:var(--green-soft); font-weight:600; margin-bottom:6px;">${member.dept}</p>
      <p>${member.bio}</p>
    </article>
  `).join("");
}

/**
 * Render Gallery + Interactive Lightbox Modal
 */
let currentLightboxIdx = 0;
let currentGalleryList = [...GALLERY_DATA];

function renderGallery() {
  const galleryGrid = document.getElementById("gallery-grid");
  const filterBar = document.getElementById("gallery-filter-bar");
  const emptyState = document.getElementById("gallery-empty");

  if (!galleryGrid) return;

  if (filterBar) {
    const cats = ["All", ...new Set(GALLERY_DATA.map(g => g.category))];
    filterBar.innerHTML = cats.map((c, i) => `
      <button class="filter-btn ${i === 0 ? 'active' : ''}" data-cat="${c}">${c}</button>
    `).join("");

    filterBar.addEventListener("click", (e) => {
      const btn = e.target.closest(".filter-btn");
      if (!btn) return;
      filterBar.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");

      const cat = btn.getAttribute("data-cat");
      currentGalleryList = cat === "All" ? GALLERY_DATA : GALLERY_DATA.filter(g => g.category === cat);
      drawGallery();
    });

    const resetBtn = document.getElementById("reset-gallery-btn");
    if (resetBtn) {
      resetBtn.addEventListener("click", () => {
        const allBtn = filterBar.querySelector('[data-cat="All"]');
        if (allBtn) allBtn.click();
      });
    }
  }

  function drawGallery() {
    if (currentGalleryList.length === 0) {
      galleryGrid.innerHTML = "";
      if (emptyState) emptyState.style.display = "block";
    } else {
      if (emptyState) emptyState.style.display = "none";
      galleryGrid.innerHTML = currentGalleryList.map((item, index) => `
        <div class="gallery-item" data-idx="${index}" tabindex="0" role="button" aria-label="View photo: ${item.title}">
          <img src="${item.image}" alt="${item.title}" loading="lazy" />
          <div class="gallery-overlay">
            <span class="gallery-cat">${item.category}</span>
            <div class="gallery-name">${item.title}</div>
          </div>
        </div>
      `).join("");
    }
  }

  drawGallery();

  // Attach Lightbox event listeners
  galleryGrid.addEventListener("click", (e) => {
    const item = e.target.closest(".gallery-item");
    if (!item) return;
    const idx = parseInt(item.getAttribute("data-idx"), 10);
    openLightbox(idx);
  });

  galleryGrid.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      const item = e.target.closest(".gallery-item");
      if (item) {
        e.preventDefault();
        const idx = parseInt(item.getAttribute("data-idx"), 10);
        openLightbox(idx);
      }
    }
  });

  initLightbox();
}

function initLightbox() {
  const lb = document.getElementById("lightbox");
  if (!lb) return;

  const closeBtn = document.getElementById("lb-close");
  const prevBtn = document.getElementById("lb-prev");
  const nextBtn = document.getElementById("lb-next");

  if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
  if (prevBtn) prevBtn.addEventListener("click", () => navigateLightbox(-1));
  if (nextBtn) nextBtn.addEventListener("click", () => navigateLightbox(1));

  lb.addEventListener("click", (e) => {
    if (e.target === lb) closeLightbox();
  });

  document.addEventListener("keydown", (e) => {
    if (lb.style.display !== "flex") return;
    if (e.key === "Escape") closeLightbox();
    if (e.key === "ArrowLeft") navigateLightbox(-1);
    if (e.key === "ArrowRight") navigateLightbox(1);
  });
}

function openLightbox(index) {
  const lb = document.getElementById("lightbox");
  if (!lb || !currentGalleryList[index]) return;

  currentLightboxIdx = index;
  updateLightboxContent();
  lb.style.display = "flex";
  document.body.style.overflow = "hidden";
}

function closeLightbox() {
  const lb = document.getElementById("lightbox");
  if (!lb) return;
  lb.style.display = "none";
  document.body.style.overflow = "";
}

function navigateLightbox(delta) {
  const total = currentGalleryList.length;
  if (total === 0) return;
  currentLightboxIdx = (currentLightboxIdx + delta + total) % total;
  updateLightboxContent();
}

function updateLightboxContent() {
  const item = currentGalleryList[currentLightboxIdx];
  if (!item) return;

  const img = document.getElementById("lb-img");
  const title = document.getElementById("lb-title");
  const cat = document.getElementById("lb-cat");
  const desc = document.getElementById("lb-desc");

  if (img) { img.src = item.image; img.alt = item.title; }
  if (title) title.textContent = item.title;
  if (cat) cat.textContent = item.category;
  if (desc) desc.textContent = item.desc;
}

// ==========================================
// 3. CLIENT-SIDE FORM VALIDATION
// ==========================================

function initFormValidation() {
  const form = document.getElementById("join-form");
  if (!form) return;

  const alertBox = document.getElementById("form-alert");
  const alertList = document.getElementById("alert-list");

  // Real-time input clearing
  form.querySelectorAll("input, select, textarea").forEach(field => {
    field.addEventListener("input", () => {
      field.classList.remove("invalid");
      const err = document.getElementById(`err-${field.id || field.name}`);
      if (err) err.classList.remove("show");
    });
  });

  form.addEventListener("submit", (e) => {
    const errors = [];

    // Helper validation functions
    function validateField(id, condition, msg) {
      const el = document.getElementById(id);
      const errEl = document.getElementById(`err-${id}`);
      if (!el) return;

      if (!condition(el.value.trim())) {
        el.classList.add("invalid");
        if (errEl) errEl.classList.add("show");
        errors.push(msg);
      } else {
        el.classList.remove("invalid");
        if (errEl) errEl.classList.remove("show");
      }
    }

    // 1. Full name: at least 2 characters, letters & spaces
    validateField("full_name", v => v.length >= 2, "Full Name must contain at least 2 characters.");

    // 2. Email: basic email pattern
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    validateField("email", v => emailPattern.test(v), "Please enter a valid email address.");

    // 3. Course: required
    validateField("course", v => v.length > 0, "Course / Department is required.");

    // 4. Study year: must select
    validateField("study_year", v => v !== "", "Please select your year of study.");

    // 5. Preferred activity: must select
    validateField("preferred_activity", v => v !== "", "Please select your preferred sport.");

    // 6. Reason/message length check
    const reasonEl = document.getElementById("reason");
    if (reasonEl && reasonEl.value.trim().length > 600) {
      reasonEl.classList.add("invalid");
      const errReason = document.getElementById("err-reason");
      if (errReason) errReason.classList.add("show");
      errors.push("Message cannot exceed 600 characters.");
    }

    // 7. Consent checkbox
    const consent = document.getElementById("consent");
    const errConsent = document.getElementById("err-consent");
    if (consent && !consent.checked) {
      if (errConsent) errConsent.classList.add("show");
      errors.push("You must agree to the communications consent policy.");
    } else if (errConsent) {
      errConsent.classList.remove("show");
    }

    // If any errors exist, block submission and show summary
    if (errors.length > 0) {
      e.preventDefault();
      if (alertBox && alertList) {
        alertList.innerHTML = errors.map(err => `<li>${err}</li>`).join("");
        alertBox.style.display = "block";
        alertBox.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    } else {
      if (alertBox) alertBox.style.display = "none";
      // Let standard POST proceed to process.php!
    }
  });
}

/**
 * Handle query parameters on join.html (e.g. ?sport=Football)
 */
function checkUrlParams() {
  const params = new URLSearchParams(window.location.search);
  const sport = params.get("sport");
  const purpose = params.get("purpose");
  const error = params.get("error");

  if (sport) {
    const actSelect = document.getElementById("preferred_activity");
    if (actSelect) {
      for (let opt of actSelect.options) {
        if (opt.value.toLowerCase() === sport.toLowerCase()) {
          opt.selected = true;
          break;
        }
      }
    }
  }

  if (purpose) {
    const purpSelect = document.getElementById("form_purpose");
    if (purpSelect) {
      if (purpose === "event") purpSelect.value = "Event enquiry";
    }
  }

  if (error) {
    const alertBox = document.getElementById("form-alert");
    const alertList = document.getElementById("alert-list");
    if (alertBox && alertList) {
      const messages = error.split("|").map(item => item.trim()).filter(Boolean);
      alertList.innerHTML = messages.map(msg => `<li>${msg}</li>`).join("");
      alertBox.style.display = "block";
      alertBox.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
}
