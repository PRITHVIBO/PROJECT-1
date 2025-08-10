document.addEventListener('DOMContentLoaded', () => {
    // --- DATA ---
    const topics = [
        { 
            id: 7, 
            title: "The Philosophy of Stoicism in Modern Life", 
            author: "philosopher_king", 
            time: "1 day ago", 
            category: "Academics", 
            tags: ["Philosophy", "Well-being"], 
            views: 350,
            summary: "Exploring how ancient Stoic principles can be applied to navigate the challenges of the 21st century, from managing stress to finding purpose. This discussion delves into the works of Seneca, Epictetus, and Marcus Aurelius...",
            fullContent: "<p>This discussion delves into the works of Seneca, Epictetus, and Marcus Aurelius. We'll analyze key tenets such as the dichotomy of control, the practice of negative visualization, and the importance of living in accordance with nature. Participants are encouraged to share their own experiences and interpretations of how these timeless philosophies can foster resilience and tranquility in a world of constant distraction and digital noise.</p><p class='mt-2'>How do you practice Stoicism in your daily life? What are the biggest challenges you face when trying to apply these principles?</p>"
        },
        { 
            id: 3, 
            title: "Study Techniques for Better Retention", 
            author: "study_guru", 
            time: "6 hours ago", 
            category: "Academics", 
            tags: ["Study Methods", "Learning"], 
            views: 287,
            summary: "A deep dive into evidence-based learning strategies that go beyond simple rereading and highlighting. We're talking about active recall, spaced repetition, the Feynman Technique, and how to combine them for maximum effect...",
            fullContent: "<p>We're talking about active recall, spaced repetition, the Feynman Technique, and how to combine them for maximum effect. This thread is for students and lifelong learners to share what works for them, what doesn't, and to build a collective resource of effective study habits. We'll also touch on the role of sleep, diet, and exercise in cognitive performance.</p><p class='mt-2'>Share your go-to study method and one tip you'd give to someone struggling with information overload!</p>"
        },
        { 
            id: 2, 
            title: "AI Impact on Future Job Markets", 
            author: "tech_analyst", 
            time: "4 hours ago", 
            category: "Technology", 
            tags: ["AI", "Career"], 
            views: 203,
            summary: "Which jobs are most at risk from AI automation? Which new roles will be created? This discussion aims to separate the hype from reality, looking at current trends and expert predictions for the next decade...",
            fullContent: "<p>This discussion aims to separate the hype from reality, looking at current trends and expert predictions for the next decade. We'll explore the impact of generative AI, large language models, and autonomous systems on various industries, from creative fields to logistics. The conversation will also cover the importance of upskilling and the ethical considerations of a workforce increasingly augmented by artificial intelligence.</p><p class='mt-2'>What skills do you think will be most valuable in an AI-driven economy?</p>"
        },
        { 
            id: 5, 
            title: "Leadership Qualities for Team Management", 
            author: "team_lead", 
            time: "10 hours ago", 
            category: "Soft Skills", 
            tags: ["Leadership", "Management"], 
            views: 198,
            summary: "What separates a good manager from a great leader? This thread is for sharing insights on essential leadership qualities, such as empathy, clear communication, and the ability to inspire and motivate a team...",
            fullContent: "<p>This thread is for sharing insights on essential leadership qualities, such as empathy, clear communication, and the ability to inspire and motivate a team. We will discuss different leadership styles (e.g., transformational, servant, democratic) and their effectiveness in different contexts. Share a story about a great leader you've worked with and what made them so effective.</p>"
        },
    ];

    // --- DOM ELEMENTS ---
    const popularTopicsList = document.getElementById('popular-topics-list');

    // --- FUNCTIONS ---
    const renderPopularTopics = () => {
        popularTopicsList.innerHTML = '';
        const sortedTopics = topics.sort((a, b) => b.views - a.views);

        if (sortedTopics.length === 0) {
            popularTopicsList.innerHTML = `<div class="text-center py-10 px-4 bg-white rounded-lg shadow-sm"><p class="text-gray-500">No discussions found.</p></div>`;
            return;
        }

        sortedTopics.forEach(topic => {
            const avatar = topic.category.substring(0, 2).toUpperCase();
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 transition-all duration-300';
            card.innerHTML = `
                <div class="p-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 topic-avatar-gradient text-white flex items-center justify-center rounded-full font-bold text-lg">
                            ${avatar}
                        </div>
                        <div class="flex-grow">
                            <h3 class="text-lg font-semibold text-gray-900">${topic.title}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Started by <strong>${topic.author}</strong> &bull; ${topic.time}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-sm text-center text-gray-600">
                            <div class="font-bold text-indigo-600">${topic.views}</div>
                            <div>views</div>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-4 text-sm leading-relaxed">${topic.summary}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="flex flex-wrap gap-2">
                            ${topic.tags.map(tag => `<span class="tag-blue px-2 py-1 text-xs font-semibold rounded-full">${tag}</span>`).join('')}
                        </div>
                        <button class="read-more-btn text-indigo-600 font-semibold text-sm hover:text-indigo-800">Read More <i class="fas fa-arrow-right ml-1 text-xs"></i></button>
                    </div>
                </div>
                <div class="expandable-content">
                    <div class="p-4 border-t border-gray-200">
                        <div class="prose prose-sm max-w-none text-gray-700">
                            ${topic.fullContent}
                        </div>
                        <!-- Reply Section -->
                        <div class="mt-6">
                            <h4 class="text-md font-semibold mb-3 text-gray-800">Join the Conversation</h4>
                            <div class="relative">
                                <textarea class="w-full h-24 p-3 border border-gray-300 rounded-lg resize-none focus:outline-none" placeholder="Write your reply..." disabled></textarea>
                                <div class="absolute inset-0 bg-gray-100/50 reply-overlay flex flex-col items-center justify-center text-center rounded-lg p-3">
                                    <p class="font-semibold text-gray-800 text-sm">You must be signed in to reply.</p>
                                    <a href="#" class="mt-2 px-4 py-1.5 bg-indigo-600 text-white font-semibold rounded-full shadow-md hover:bg-indigo-700 text-xs">
                                        Sign In or Sign Up
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            popularTopicsList.appendChild(card);
        });

        // Add event listeners to all "Read More" buttons
        document.querySelectorAll('.read-more-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const card = e.target.closest('.bg-white');
                const content = card.querySelector('.expandable-content');
                const isExpanded = content.classList.contains('expanded');

                if (isExpanded) {
                    content.classList.remove('expanded');
                    e.target.innerHTML = 'Read More <i class="fas fa-arrow-right ml-1 text-xs"></i>';
                } else {
                    content.classList.add('expanded');
                    e.target.innerHTML = 'Read Less <i class="fas fa-arrow-up ml-1 text-xs"></i>';
                }
            });
        });
    };

    // --- INITIAL RENDER ---
    renderPopularTopics();
});
