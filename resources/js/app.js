import "../css/app.css";
import {
    validators,
    validateValues,
    clearFieldErrors,
    showFieldErrors,
    normalizeApiErrors,
} from "./validation";

const TOKEN_KEY = "ticket_booking_access_token";
let currentUserCache = null;

const initMobileNav = () => {
    const toggle = document.querySelector("[data-sidebar-toggle]");
    const sidebar = document.querySelector("[data-sidebar]");

    if (!toggle || !sidebar) return;

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
    });
};

const apiAuth = {
    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    },
    setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    },
    clearToken() {
        localStorage.removeItem(TOKEN_KEY);
        currentUserCache = null;
    },
};

const apiRequest = async (url, options = {}) => {
    const headers = {
        Accept: "application/json",
        ...(options.body ? { "Content-Type": "application/json" } : {}),
        ...(options.headers ?? {}),
    };

    const token = apiAuth.getToken();
    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(url, {
        ...options,
        headers,
    });

    let payload = null;
    try {
        payload = await response.json();
    } catch (_) {
        payload = null;
    }

    return {
        ok: response.ok,
        status: response.status,
        data: payload,
    };
};

const firstApiError = (data) => {
    if (data?.errors) {
        const first = Object.values(data.errors)[0];
        if (Array.isArray(first) && first[0]) {
            return first[0];
        }
    }

    return data?.message || "Đã xảy ra lỗi";
};

const getRoleName = (user) => {
    const role = user?.role;
    if (typeof role === "string") return role.toLowerCase();
    if (role && typeof role.name === "string") return role.name.toLowerCase();
    return null;
};

const getCurrentUser = async ({ forceRefresh = false } = {}) => {
    if (!apiAuth.getToken()) {
        currentUserCache = null;
        return null;
    }

    if (!forceRefresh && currentUserCache) {
        return currentUserCache;
    }

    const result = await apiRequest("/api/users/profile");

    if (result.ok && result.data?.data) {
        currentUserCache = result.data.data;
        return currentUserCache;
    }

    if (result.status === 401) {
        apiAuth.clearToken();
    }

    return null;
};

const hasAnyRole = (user, roles = []) => {
    if (!roles.length) return true;

    const normalizedRoles = roles.map((role) => String(role).toLowerCase());
    const currentRole = getRoleName(user);

    return Boolean(currentRole && normalizedRoles.includes(currentRole));
};

const goToLogin = () => {
    const next = encodeURIComponent(window.location.pathname || "/");
    window.location.href = `/login?next=${next}`;
};

const requireAuth = async ({ redirect = true } = {}) => {
    if (!apiAuth.getToken()) {
        if (redirect) goToLogin();
        return {
            ok: false,
            reason: "unauthenticated",
            message: "Bạn cần đăng nhập để tiếp tục.",
        };
    }

    const user = await getCurrentUser();

    if (!user) {
        if (redirect) goToLogin();
        return {
            ok: false,
            reason: "unauthenticated",
            message: "Phiên đăng nhập không hợp lệ hoặc đã hết hạn.",
        };
    }

    return {
        ok: true,
        user,
    };
};

const requireRoles = async (roles, { redirectUnauthenticated = true } = {}) => {
    const auth = await requireAuth({ redirect: redirectUnauthenticated });

    if (!auth.ok) {
        return auth;
    }

    if (!hasAnyRole(auth.user, roles)) {
        return {
            ok: false,
            reason: "forbidden",
            user: auth.user,
            message: "Bạn không có quyền truy cập.",
        };
    }

    return auth;
};

document.addEventListener("DOMContentLoaded", () => {
    initMobileNav();
});

window.ApiAuth = apiAuth;
window.apiRequest = apiRequest;
window.FormValidation = {
    validators,
    validateValues,
    clearFieldErrors,
    showFieldErrors,
    normalizeApiErrors,
};
window.firstApiError = firstApiError;
window.WebGuard = {
    getCurrentUser,
    getRoleName,
    hasAnyRole,
    requireAuth,
    requireRoles,
};


