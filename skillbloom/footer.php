<footer style="background: var(--dark); color: rgba(255,255,255,0.7); padding: 3rem 0 1.5rem; margin-top: auto;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 2.5rem; margin-bottom: 2.5rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #2563EB, #0EA5E9); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🌱</div>
                    <span style="font-size: 1.375rem; font-weight: 800; color: white;">SkillBloom</span>
                </div>
                <p style="font-size: 0.9rem; line-height: 1.8; max-width: 300px;">Grow your skills with expert-led online courses. Learn at your own pace from anywhere.</p>
                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <a href="#" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 1.25rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">𝕏</a>
                    <a href="#" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 1.25rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">in</a>
                    <a href="#" style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 1.25rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">yt</a>
                </div>
            </div>
            <div>
                <h4 style="color: white; font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Platform</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.625rem;">
                    <?php foreach (['Browse Courses' => '/user/courses.php', 'My Learning' => '/user/my-courses.php', 'Certificates' => '/user/my-certificates.php', 'Dashboard' => '/user/dashboard.php'] as $label => $link): ?>
                    <li><a href="<?php echo SITE_URL . $link; ?>" style="text-decoration: none; color: rgba(255,255,255,0.6); font-size: 0.875rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'"><?php echo $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 style="color: white; font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Support</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.625rem;">
                    <?php foreach (['Help Center', 'Contact Us', 'Privacy Policy', 'Terms of Service'] as $item): ?>
                    <li><a href="#" style="text-decoration: none; color: rgba(255,255,255,0.6); font-size: 0.875rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'"><?php echo $item; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 style="color: white; font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Contact</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.625rem; font-size: 0.875rem;">
                    <li>📧 info@skillbloom.com</li>
                    <li>📞 +1 (555) 123-4567</li>
                    <li>🌍 Available worldwide</li>
                </ul>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
            <span>&copy; <?php echo date('Y'); ?> SkillBloom. All rights reserved.</span>
            <span>Made with ❤️ for learners worldwide</span>
        </div>
    </div>
</footer>
</body>
</html>
