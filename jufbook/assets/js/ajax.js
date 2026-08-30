/**
 * ajax.js - Fetch API logic for form submissions and dynamic loading
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Newsletter Subscription AJAX Form
    const subscribeForm = document.getElementById('subscribeForm');
    const subMessage = document.getElementById('subMessage');
    const subBtn = document.getElementById('subBtn');

    if (subscribeForm) {
        subscribeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('subEmail').value;
            const csrf_token = document.querySelector('input[name="csrf_token"]').value;
            
            // UI Loading state
            subBtn.textContent = 'অপেক্ষা করুন...';
            subBtn.disabled = true;

            try {
                // In production, create a file named ajax_subscribe.php in the root to handle this POST request
                /*
                const response = await fetch('ajax_subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}&csrf_token=${encodeURIComponent(csrf_token)}`
                });
                
                const data = await response.json();
                */

                // Simulating network request for blueprint demonstration
                setTimeout(() => {
                    subMessage.style.display = 'block';
                    subMessage.style.color = '#3E5641'; // Success Green
                    subMessage.innerHTML = 'ধন্যবাদ! আপনি সফলভাবে সাবস্ক্রাইব করেছেন।';
                    subscribeForm.reset();
                    
                    subBtn.textContent = 'সাবস্ক্রাইব';
                    subBtn.disabled = false;
                }, 1000);

            } catch (error) {
                subMessage.style.display = 'block';
                subMessage.style.color = '#D35446'; // Error Red
                subMessage.innerHTML = 'দুঃখিত, কোনো একটি সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                
                subBtn.textContent = 'সাবস্ক্রাইব';
                subBtn.disabled = false;
            }
        });
    }
});