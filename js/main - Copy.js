// Add to cart functionality
function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart!');
            updateCartCount();
        } else {
            alert(data.message || 'Error adding product to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
    });
}

// Update cart count in navigation
function updateCartCount() {
    fetch('get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        const cartLink = document.querySelector('.nav-links a[href="cart.php"]');
        if (cartLink) {
            cartLink.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${data.count})`;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});

// Product image preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;

    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;

    switch(strength) {
        case 0:
        case 1:
            strengthBar.style.width = '20%';
            strengthBar.style.backgroundColor = '#ff4444';
            break;
        case 2:
            strengthBar.style.width = '40%';
            strengthBar.style.backgroundColor = '#ffbb33';
            break;
        case 3:
            strengthBar.style.width = '60%';
            strengthBar.style.backgroundColor = '#ffeb3b';
            break;
        case 4:
            strengthBar.style.width = '80%';
            strengthBar.style.backgroundColor = '#00C851';
            break;
        case 5:
            strengthBar.style.width = '100%';
            strengthBar.style.backgroundColor = '#007E33';
            break;
    }
}

// Mobile navigation toggle
const mobileMenuButton = document.querySelector('.mobile-menu-button');
const navLinks = document.querySelector('.nav-links');

if (mobileMenuButton && navLinks) {
    mobileMenuButton.addEventListener('click', () => {
        navLinks.classList.toggle('show');
    });
}

// Product filter
function filterProducts(category) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        if (category === 'all' || product.dataset.category === category) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Price range filter
function filterByPrice(min, max) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        const price = parseFloat(product.dataset.price);
        if (price >= min && price <= max) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// Sort products
function sortProducts(method) {
    const productGrid = document.querySelector('.product-grid');
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        const priceA = parseFloat(a.dataset.price);
        const priceB = parseFloat(b.dataset.price);
        
        if (method === 'price-low-high') {
            return priceA - priceB;
        } else if (method === 'price-high-low') {
            return priceB - priceA;
        }
    });

    productGrid.innerHTML = '';
    products.forEach(product => productGrid.appendChild(product));
}

// Search functionality
function searchProducts(query) {
    const products = document.querySelectorAll('.product-card');
    const searchQuery = query.toLowerCase();

    products.forEach(product => {
        const name = product.querySelector('h3').textContent.toLowerCase();
        const description = product.querySelector('p').textContent.toLowerCase();

        if (name.includes(searchQuery) || description.includes(searchQuery)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
}

// PC Build Recommender
document.addEventListener('DOMContentLoaded', function() {
    const buildForm = document.getElementById('buildRecommenderForm');
    if (buildForm) {
        buildForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const purpose = document.getElementById('purpose').value;
            const budget = document.getElementById('budget').value;
            
            // Validate form data
            if (!purpose || !budget) {
                alert('Please select both purpose and budget');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Getting Recommendations...';
            submitButton.disabled = true;
            
            // Send request
            fetch('api/recommend_build.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    purpose: purpose,
                    budget: budget
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayRecommendation(data.build);
                } else {
                    alert(data.error || 'Could not find a suitable build. Please try different criteria.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while getting recommendations. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }
});

function displayRecommendation(build) {
    const recommendationResult = document.getElementById('recommendationResult');
    const resultContainer = recommendationResult.querySelector('.recommended-build');
    
    // Create build card
    const buildCard = document.createElement('div');
    buildCard.className = 'build-card';
    
    // Build header
    const buildHeader = document.createElement('div');
    buildHeader.className = 'build-header';
    buildHeader.innerHTML = `
        <h4>${build.name}</h4>
        <p>${build.description}</p>
    `;
    
    // Build components
    const buildComponents = document.createElement('div');
    buildComponents.className = 'build-components';
    
    Object.entries(build.components).forEach(([component, product]) => {
        const componentDiv = document.createElement('div');
        componentDiv.className = 'component';
        componentDiv.innerHTML = `
            <h5>${component}</h5>
            <p>${product.name}</p>
            <p>${product.price.toLocaleString('en-LK', { style: 'currency', currency: 'LKR' })}</p>
        `;
        buildComponents.appendChild(componentDiv);
    });
    
    // Build footer
    const buildFooter = document.createElement('div');
    buildFooter.className = 'build-footer';
    buildFooter.innerHTML = `
        <h4>Total Price: ${build.total_price.toLocaleString('en-LK', { style: 'currency', currency: 'LKR' })}</h4>
    `;
    
    // Assemble build card
    buildCard.appendChild(buildHeader);
    buildCard.appendChild(buildComponents);
    buildCard.appendChild(buildFooter);
    
    // Clear and append new build card
    resultContainer.innerHTML = '';
    resultContainer.appendChild(buildCard);
    
    // Show the recommendation result
    recommendationResult.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    const recommendationForm = document.getElementById('recommendationForm');
    
    if (recommendationForm) {
        recommendationForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission
            
            // Get form data
            const purpose = document.getElementById('purpose').value;
            const budget = document.getElementById('budget').value;
            
            // Validate form data
            if (!purpose || !budget) {
                alert('Please select both purpose and budget');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.textContent = 'Getting Recommendations...';
            submitButton.disabled = true;
            
            // Send request
            fetch('api/recommend_build.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    purpose: purpose,
                    budget: budget
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayRecommendation(data.build);
                } else {
                    alert(data.error || 'Could not find a suitable build. Please try different criteria.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while getting recommendations. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.textContent = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }
});

// Q&A Section
document.addEventListener('DOMContentLoaded', function() {
    const askQuestionForm = document.getElementById('askQuestionForm');
    const questionsList = document.getElementById('questionsList');
    const productId = new URLSearchParams(window.location.search).get('id');

    if (askQuestionForm) {
        askQuestionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const question = document.getElementById('question').value;
            
            if (!question.trim()) {
                alert('Please enter a question');
                return;
            }

            fetch('api/qa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'ask_question',
                    product_id: productId,
                    question: question
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Question submitted successfully!');
                    askQuestionForm.reset();
                    loadQuestions();
                } else {
                    alert('Error submitting question: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your question. Please try again.');
            });
        });
    }

    // Load questions when page loads
    loadQuestions();

    function loadQuestions() {
        fetch(`api/qa.php?action=get_questions&product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQuestions(data.questions);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                questionsList.innerHTML = '<p>Error loading questions. Please try again later.</p>';
            });
    }

    function displayQuestions(questions) {
        const questionsHtml = questions.map(question => `
            <div class="qa-item">
                <div class="question">
                    <div class="qa-header">
                        <span class="asker">${question.asker_name}</span>
                        <span class="date">${new Date(question.question_date).toLocaleDateString()}</span>
                    </div>
                    <p>${question.question}</p>
                </div>
                
                ${question.answer ? `
                <div class="answer">
                    <div class="qa-header">
                        <span class="answerer">${question.answerer_name}</span>
                        <span class="date">${new Date(question.answer_date).toLocaleDateString()}</span>
                    </div>
                    <p>${question.answer}</p>
                </div>
                ` : ''}
            </div>
        `).join('');

        questionsList.innerHTML = questionsHtml || '<p>No questions or answers yet. Be the first to ask!</p>';
    }
});

// Hero Slider
document.addEventListener('DOMContentLoaded', function() {
    const hero = document.querySelector('.hero');
    const dots = document.querySelectorAll('.dot');
    const backgrounds = [
        'url("/kds/images/hero-bg1.jpg")',
        'url("/kds/images/hero-bg2.jpg")',
        'url("/kds/images/hero-bg3.jpg")'
    ];
    let currentSlide = 0;
    let slideInterval;

    // Preload images
    backgrounds.forEach(bg => {
        const img = new Image();
        img.src = bg.replace('url("', '').replace('")', '');
        img.onerror = () => console.error('Failed to load image:', img.src);
        img.onload = () => console.log('Successfully loaded image:', img.src);
    });

    function changeBackground(index) {
        // Update background
        const newBg = `linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), ${backgrounds[index]}`;
        hero.style.backgroundImage = newBg;
        
        // Update dots
        dots.forEach(dot => dot.classList.remove('active'));
        dots[index].classList.add('active');

        // Debug log
        console.log('Changed background to:', newBg);
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % backgrounds.length;
        changeBackground(currentSlide);
    }

    // Add click events to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            changeBackground(currentSlide);
            // Reset interval
            clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 3000);
        });
    });

    // Start automatic slideshow
    changeBackground(0); // Set initial background
    slideInterval = setInterval(nextSlide, 3000);

    // Debug info
    console.log('Hero element:', hero);
    console.log('Dot elements:', dots);
    console.log('Available backgrounds:', backgrounds);
});
