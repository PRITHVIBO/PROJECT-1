
document.addEventListener('DOMContentLoaded', () => {
            // --- DATA ---
    let categories = ['All', 'Soft Skills', 'Technology', 'Academics', 'Sports'];

    const topics = [
        { id: 1, title: "Effective Communication Skills in Remote Work", author: "sarah_pro", time: "2 hours ago", category: "Soft Skills", tags: ["Communication", "Remote Work"], views: 156 },
        { id: 2, title: "AI Impact on Future Job Markets", author: "tech_analyst", time: "4 hours ago", category: "Technology", tags: ["AI", "Career"], views: 203 },
        { id: 3, title: "Study Techniques for Better Retention", author: "study_guru", time: "6 hours ago", category: "Academics", tags: ["Study Methods", "Learning"], views: 287 },
        { id: 4, title: "Mental Preparation for Competitive Sports", author: "coach_mike", time: "8 hours ago", category: "Sports", tags: ["Mental Health", "Competition"], views: 145 },
        { id: 5, title: "Leadership Qualities for Team Management", author: "team_lead", time: "10 hours ago", category: "Soft Skills", tags: ["Leadership", "Management"], views: 198 },
        { id: 6, title: "Cybersecurity Best Practices for Students", author: "security_expert", time: "12 hours ago", category: "Technology", tags: ["Cybersecurity", "Safety"], views: 167 },
        { id: 7, title: "The Philosophy of Stoicism in Modern Life", author: "philosopher_king", time: "1 day ago", category: "Academics", tags: ["Philosophy", "Well-being"], views: 350 },
    ];

    let activeCategory = 'All';

            // --- DOM ELEMENTS ---
    const categoryBar = document.getElementById('category-choice-bar');
    const topicsList = document.getElementById('topics-list');

            // --- FUNCTIONS ---
    const renderCategories = () => {
        categoryBar.innerHTML = '';
        categories.forEach(category => {
            const chip = document.createElement('button');
            const isActive = activeCategory === category;
            chip.className = `px-4 py-2 rounded-full text-sm font-semibold transition-all duration-300 whitespace-nowrap shadow-sm border border-transparent ${
                isActive 
                ? 'active-category-chip shadow-md' 
                : 'bg-white text-gray-700 hover:border-indigo-300'
            }`;
            chip.textContent = category;
            chip.addEventListener('click', () => {
                activeCategory = category;
                renderCategories();
                renderTopics();
            });
            categoryBar.appendChild(chip);
        });
                // The "Add Category" button is no longer created here.
    };

    const renderTopics = () => {
        topicsList.innerHTML = '';
        const filteredTopics = activeCategory === 'All'
        ? topics
        : topics.filter(t => t.category === activeCategory);

        if (filteredTopics.length === 0) {
            topicsList.innerHTML = `<div class="text-center py-10 px-4 bg-white rounded-lg shadow-sm"><p class="text-gray-500">No discussions in this category yet. Be the first to start one!</p></div>`;
            return;
        }

        filteredTopics.forEach(topic => {
            const avatar = topic.category.substring(0, 2).toUpperCase();
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:shadow-md hover:border-indigo-300 transition-all duration-300';
            card.innerHTML = `
                        <div class="flex-shrink-0 w-12 h-12 topic-avatar-gradient text-white flex items-center justify-center rounded-full font-bold text-lg">
                            ${avatar}
                        </div>
                        <div class="flex-grow">
                            <h3 class="text-lg font-semibold text-gray-900 hover:text-indigo-600 cursor-pointer">${topic.title}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Started by <strong>${topic.author}</strong> &bull; ${topic.time}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                ${topic.tags.map(tag => `<span class="tag-blue px-2 py-1 text-xs font-semibold rounded-full">${tag}</span>`).join('')}
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-sm text-center text-gray-600">
                            <div class="font-bold text-indigo-600">${topic.views}</div>
                            <div>views</div>
                        </div>
            `;
            topicsList.appendChild(card);
        });
    };

            // --- INITIAL RENDER ---
    renderCategories();
    renderTopics();
});