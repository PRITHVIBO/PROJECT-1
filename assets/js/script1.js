// DOM elements
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const sortSelect = document.getElementById('sortSelect');
const topicsList = document.getElementById('topicsList');
const categoryItems = document.querySelectorAll('.category-item');
const pageButtons = document.querySelectorAll('.page-btn');

// Sample data for topics
let topics = [
    {
        id: 1,
        title: "Effective Communication Skills in Remote Work",
        author: "sarah_pro",
        time: "2 hours ago",
        category: "softskills",
        tags: ["Communication", "Remote Work"],
        views: 156
    },
    {
        id: 2,
        title: "AI Impact on Future Job Markets",
        author: "tech_analyst",
        time: "4 hours ago",
        category: "technology",
        tags: ["Artificial Intelligence", "Career"],
        views: 203
    },
    {
        id: 3,
        title: "Study Techniques for Better Retention",
        author: "study_guru",
        time: "6 hours ago",
        category: "academics",
        tags: ["Study Methods", "Learning"],
        views: 287
    },
    {
        id: 4,
        title: "Mental Preparation for Competitive Sports",
        author: "coach_mike",
        time: "8 hours ago",
        category: "sports",
        tags: ["Mental Health", "Competition"],
        views: 145
    },
    {
        id: 5,
        title: "Leadership Qualities for Team Management",
        author: "team_lead",
        time: "10 hours ago",
        category: "softskills",
        tags: ["Leadership", "Management"],
        views: 198
    },
    {
        id: 6,
        title: "Cybersecurity Best Practices for Students",
        author: "security_expert",
        time: "12 hours ago",
        category: "technology",
        tags: ["Cybersecurity", "Safety"],
        views: 167
    }
];

let filteredTopics = [...topics];
let currentPage = 1;

// Search functionality
function handleSearch() {
    const query = searchInput.value.toLowerCase().trim();
    if (query === '') {
        filteredTopics = [...topics];
    } else {
        filteredTopics = topics.filter(topic => 
            topic.title.toLowerCase().includes(query) ||
            topic.author.toLowerCase().includes(query) ||
            topic.tags.some(tag => tag.toLowerCase().includes(query))
        );
    }
    renderTopics();
}

// Sort functionality
function sortTopics(criteria) {
    switch(criteria) {
        case 'recent':
            filteredTopics.sort((a, b) => new Date(b.time) - new Date(a.time));
            break;
        case 'popular':
            filteredTopics.sort((a, b) => b.views - a.views);
            break;
    }
    renderTopics();
}

// Filter by category
function filterByCategory(category) {
    if (category === 'all') {
        filteredTopics = [...topics];
    } else {
        filteredTopics = topics.filter(topic => topic.category === category);
    }
    renderTopics();
}

// Render topics
function renderTopics() {
    topicsList.innerHTML = '';
    
    filteredTopics.forEach(topic => {
        const topicCard = document.createElement('div');
        topicCard.className = 'topic-card';
        topicCard.setAttribute('data-category', topic.category);
        
        const avatar = topic.category.substring(0, 2).toUpperCase();
        
        topicCard.innerHTML = `
            <div class="topic-avatar">${avatar}</div>
            <div class="topic-content">
                <h3 class="topic-title">${topic.title}</h3>
                <p class="topic-meta">Started by <strong>${topic.author}</strong> • ${topic.time}</p>
                <div class="topic-tags">
                    ${topic.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                </div>
            </div>
            <div class="topic-stats">
                <div class="stat-item">
                    <span class="stat-number">${topic.views}</span>
                    <span class="stat-label">Views</span>
                </div>
            </div>
        `;
        
        topicCard.addEventListener('click', () => {
            console.log(`Clicked on topic: ${topic.title}`);
            // Add navigation to topic detail page here
        });
        
        topicsList.appendChild(topicCard);
    });
}

// Event listeners
searchBtn.addEventListener('click', handleSearch);
searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        handleSearch();
    }
});

sortSelect.addEventListener('change', (e) => {
    sortTopics(e.target.value);
});

categoryItems.forEach(item => {
    item.addEventListener('click', () => {
        const category = item.getAttribute('data-category');
        filterByCategory(category);
        
        // Update active state
        categoryItems.forEach(cat => cat.classList.remove('active'));
        item.classList.add('active');
    });
});

// Pagination
pageButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const page = btn.getAttribute('data-page');
        
        if (page === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (page === 'next') {
            currentPage++;
        } else if (!isNaN(page)) {
            currentPage = parseInt(page);
        }
        
        // Update active page button
        pageButtons.forEach(b => b.classList.remove('active'));
        const activeBtn = document.querySelector(`[data-page="${currentPage}"]`);
        if (activeBtn) activeBtn.classList.add('active');
        
        console.log('Current page:', currentPage);
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    renderTopics();
    console.log('Forum loaded successfully!');
});