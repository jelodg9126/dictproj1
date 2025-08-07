console.log("✅ dashboard.js loaded");

document.addEventListener("DOMContentLoaded", function () {
    // === PASSWORD TOGGLE (new + confirm) ===
    const toggleBtn = document.getElementById("toggleNewPassword");
    const passwordInput = document.getElementById("newPassword");
    let showPassword = false;

    toggleBtn?.addEventListener("click", function () {
        showPassword = !showPassword;
        passwordInput.type = showPassword ? "text" : "password";
        toggleBtn.innerHTML = `<i data-lucide="${showPassword ? 'eye' : 'eye-closed'}" class="w-5 h-5"></i>`;
        if (window.lucide) lucide.createIcons();
    });

    const toggleConfirmBtn = document.getElementById("toggleConfirmPassword");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    let showConfirmPassword = false;

    toggleConfirmBtn?.addEventListener("click", function () {
        showConfirmPassword = !showConfirmPassword;
        confirmPasswordInput.type = showConfirmPassword ? "text" : "password";
        toggleConfirmBtn.innerHTML = `<i data-lucide="${showConfirmPassword ? 'eye' : 'eye-closed'}" class="w-5 h-5"></i>`;
        if (window.lucide) lucide.createIcons();
    });

    if (window.lucide) lucide.createIcons();

    // === DROPDOWN ===
    const iconButton = document.getElementById('iconButton');
    const dropdown = document.getElementById('dropdown');

    iconButton?.addEventListener('click', () => {
        dropdown?.classList.toggle('hidden');
    });

    window.addEventListener('click', (e) => {
        if (!iconButton?.contains(e.target) && !dropdown?.contains(e.target)) {
            dropdown?.classList.add('hidden');
        }
    });

    // === PROFILE MODAL ===
    const openProfileBtn = document.getElementById('openProfileBtn');
    const profileModal = document.getElementById('profileModal');
    const closeProfileBtn = document.getElementById('closeProfileBtn');

    openProfileBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        profileModal?.classList.remove('scale-0', 'opacity-0');
        dropdown?.classList.add('hidden');
    });

    closeProfileBtn?.addEventListener('click', () => {
        profileModal?.classList.add('scale-0', 'opacity-0');
    });

    // === EMAIL MODAL ===
    const emailConfirmModal = document.getElementById("emailConfirmModal");
    const openEmailConfirmBtn = document.getElementById("openEmailConfirmBtn");
    const closeEmailConfirmBtn = document.getElementById("closeEmailConfirmBtn");

    openEmailConfirmBtn?.addEventListener("click", (e) => {
        e.preventDefault();
        emailConfirmModal?.classList.remove("scale-0", "opacity-0", "hidden");
        if (window.lucide) lucide.createIcons();
    });

    closeEmailConfirmBtn?.addEventListener("click", () => {
        emailConfirmModal?.classList.add("scale-0", "opacity-0");
        setTimeout(() => emailConfirmModal?.classList.add("hidden"), 300);
    });

    // === OTP MODAL ===
    const otpModal = document.getElementById("otpModal");
    const closeOtpModalBtn = document.getElementById("closeOtpModalBtn");

    closeOtpModalBtn?.addEventListener("click", () => {
        otpModal?.classList.add("scale-0", "opacity-0");
        setTimeout(() => otpModal?.classList.add("hidden"), 300);
    });

    // === RESET PASSWORD MODAL ===
    const modal = document.getElementById("modal");
    const closeModalBtn = document.getElementById("closeModalBtn");

    closeModalBtn?.addEventListener("click", () => {
        modal?.classList.add("scale-0", "opacity-0");
        setTimeout(() => modal?.classList.add("hidden"), 300);
    });

    // === EMAIL & OTP FLOW ===
    const confirmEmailInput = document.getElementById("confirmEmail");
    const otpInput = document.getElementById("otpInput");
    const verifyOtpBtn = document.getElementById("verifyOtpBtn");
    const proceedToResetBtn = document.getElementById("proceedToReset");

    proceedToResetBtn?.addEventListener("click", async (e) => {
        e.preventDefault();

        const email = confirmEmailInput.value.trim();
        if (!email) {
            Swal.fire({
                icon: "warning",
                title: "Email Required",
                text: "Please enter your email before proceeding.",
            });
            return;
        }

        // Show a loading state
        Swal.fire({
            title: "Sending OTP...",
            text: "Please wait while we send the code to your email.",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        try {
            const res = await fetch("/dictproj1/phpmailer.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email }),
            });

            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "OTP Sent!",
                    html: `
                    An OTP has been sent to:<br>
                    <strong>${email}</strong><br>
                    Please check your inbox or spam folder.
                `,
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });

                // Hide email confirm modal and show OTP modal
                emailConfirmModal?.classList.add("scale-0", "opacity-0");
                setTimeout(() => {
                    emailConfirmModal?.classList.add("hidden");
                    otpModal?.classList.remove("scale-0", "opacity-0", "hidden");
                    if (window.lucide) lucide.createIcons?.();
                }, 300);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Failed to Send OTP",
                    text: data.message || "Something went wrong.",
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to connect. Please try again.",
            });
        }
    });

    verifyOtpBtn?.addEventListener("click", async () => {
        const otp = otpInput.value.trim();
        const email = confirmEmailInput.value.trim().toLowerCase();

        if (!otp || !email) {
            Swal.fire({
                icon: "warning",
                title: "Missing Fields",
                text: "Please enter both the OTP and your email.",
            });
            return;
        }

        verifyOtpBtn.disabled = true;
        verifyOtpBtn.textContent = "Verifying...";

        try {
            const res = await fetch("/dictproj1/verify-otp.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ otp, email }),
            });

            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "OTP Verified!",
                    text: "You may now reset your password.",
                    timer: 2000,
                    showConfirmButton: false,
                });

                otpModal?.classList.add("scale-0", "opacity-0");
                setTimeout(() => {
                    otpModal?.classList.add("hidden");
                    modal?.classList.remove("scale-0", "opacity-0", "hidden");
                    if (window.lucide) lucide.createIcons();
                    const value = newPasswordInput?.value ?? "";
                    updatePasswordValidation(value);
                }, 300);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Verification Failed",
                    text: data.message,
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Something went wrong while verifying OTP. Please try again.",
            });
        } finally {
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.textContent = "Verify OTP";
        }
    });

    // === PASSWORD VALIDATION RULES ===
    const newPasswordInput = document.getElementById("newPassword");
    const confirmPasswordError = document.getElementById("confirmPasswordError");
    const confirmBtn = document.getElementById("confirmButton");

    const validationRules = {
        length: (val) => val.length >= 8,
        uppercase: (val) => /[A-Z]/.test(val),
        number: (val) => /[0-9]/.test(val),
        special: (val) => /[!@#$%^&*(),.?":{}|<>]/.test(val),
    };

    const updatePasswordValidation = (value) => {
        Object.keys(validationRules).forEach((ruleKey) => {
            const ruleItem = document.querySelector(`[data-rule="${ruleKey}"]`);
            if (!ruleItem) return;

            ruleItem.querySelectorAll("[data-lucide]").forEach(icon => icon.remove());

            const isValid = validationRules[ruleKey](value);

            const icon = document.createElement("i");
            icon.setAttribute("data-lucide", isValid ? "circle-check-big" : "circle");
            icon.className = `w-5 h-5 transition-all ${isValid ? "text-green-600" : "text-gray-400"}`;
            ruleItem.prepend(icon);
        });

        if (window.lucide) lucide.createIcons();
    };

    // === CONFIRM PASSWORD VALIDATION ===
    const validatePasswords = () => {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        const allValid = Object.values(validationRules).every((fn) => fn(password));
        const match = password && confirmPassword && password === confirmPassword;

        newPasswordInput.classList.remove("border-red-500", "border-green-600", "border-2");
        confirmPasswordInput.classList.remove("border-red-500", "border-green-600", "border-2");

        if (match && allValid) {
            newPasswordInput.classList.add("border-2", "border-green-600");
            confirmPasswordInput.classList.add("border-2", "border-green-600");
            confirmPasswordError.classList.add("hidden");
            confirmBtn.removeAttribute("disabled");
        } else {
            confirmBtn.setAttribute("disabled", "true");

            if (confirmPassword.length > 0 && password !== confirmPassword) {
                confirmPasswordError.classList.remove("hidden");
                confirmPasswordInput.classList.add("border-2", "border-red-500");
            } else {
                confirmPasswordError.classList.add("hidden");
            }
        }
    };

    newPasswordInput?.addEventListener("input", () => {
        updatePasswordValidation(newPasswordInput.value);
        validatePasswords();
    });

    confirmPasswordInput?.addEventListener("input", validatePasswords);

    confirmBtn?.addEventListener("click", async (e) => {
        e.preventDefault();

        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const allValid = Object.values(validationRules).every((fn) => fn(password));
        const match = password === confirmPassword;

        if (!allValid || !match) {
            Swal.fire({
                icon: "error",
                title: "Validation Failed",
                text: "Please make sure your password meets all criteria and matches the confirmation.",
                confirmButtonColor: "#e3342f"
            });
            return;
        }

        const formData = new FormData();
        formData.append("newPassword", password);
        formData.append("confirmPassword", confirmPassword);

        try {
            const res = await fetch("/dictproj1/public/handler/resetPasswordHandler.php", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if (data.status === "success") {
                let timerInterval;

                Swal.fire({
                    icon: "success",
                    title: "Password Updated",
                    html: "You will be logged out in <b>20</b> seconds.",
                    timer: 20000, // 20 seconds
                    timerProgressBar: true,
                    confirmButtonText: "Logout Now",
                    didOpen: () => {
                        const b = Swal.getHtmlContainer().querySelector("b");
                        timerInterval = setInterval(() => {
                            const remainingTime = Math.ceil(Swal.getTimerLeft() / 1000);
                            b.textContent = remainingTime;
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(() => {
                    window.location.href = "/dictproj1/index.php?page=logout";
                });

                // Improved modal closing (fade & hide)
                modal?.classList.add("scale-0", "opacity-0");
                modal?.addEventListener("transitionend", function handleTransitionEnd() {
                    modal?.classList.add("hidden");
                    modal?.removeEventListener("transitionend", handleTransitionEnd);
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Something went wrong.",
                    confirmButtonColor: "#e3342f"
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: "Unable to reset password. Please try again.",
            });
        }
    });

});
