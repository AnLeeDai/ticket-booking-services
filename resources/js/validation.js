const isEmpty = (value) => {
    if (value === null || value === undefined) return true;
    if (typeof value === "string") return value.trim() === "";
    if (Array.isArray(value)) return value.length === 0;
    return false;
};

const validators = {
    required: (value, message = "Trường này là bắt buộc") =>
        isEmpty(value) ? message : null,

    email: (value, message = "Email không hợp lệ") => {
        if (isEmpty(value)) return null;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value).trim()) ? null : message;
    },

    minLength: (value, min, message) => {
        if (isEmpty(value)) return null;
        return String(value).trim().length >= min ? null : (message || `Tối thiểu ${min} ký tự`);
    },

    maxLength: (value, max, message) => {
        if (isEmpty(value)) return null;
        return String(value).trim().length <= max ? null : (message || `Tối đa ${max} ký tự`);
    },

    number: (value, message = "Giá trị phải là số") => {
        if (isEmpty(value)) return null;
        return Number.isNaN(Number(value)) ? message : null;
    },

    min: (value, min, message) => {
        if (isEmpty(value)) return null;
        return Number(value) >= min ? null : (message || `Giá trị tối thiểu là ${min}`);
    },

    max: (value, max, message) => {
        if (isEmpty(value)) return null;
        return Number(value) <= max ? null : (message || `Giá trị tối đa là ${max}`);
    },

    date: (value, message = "Ngày không hợp lệ") => {
        if (isEmpty(value)) return null;
        return Number.isNaN(new Date(value).getTime()) ? message : null;
    },

    enum: (value, list, message = "Giá trị không hợp lệ") => {
        if (isEmpty(value)) return null;
        return list.includes(value) ? null : message;
    },

    regex: (value, pattern, message = "Dữ liệu không đúng định dạng") => {
        if (isEmpty(value)) return null;
        return pattern.test(String(value)) ? null : message;
    },

    confirmed: (value, otherValue, message = "Xác nhận không khớp") => {
        if (isEmpty(value)) return null;
        return value === otherValue ? null : message;
    },

    url: (value, message = "URL không hợp lệ") => {
        if (isEmpty(value)) return null;
        try {
            // eslint-disable-next-line no-new
            new URL(value);
            return null;
        } catch (_) {
            return message;
        }
    },

    uuid: (value, message = "ID không hợp lệ") => {
        if (isEmpty(value)) return null;
        return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(value))
            ? null
            : message;
    },
};

const validateValues = (values, schema) => {
    const errors = {};

    Object.entries(schema).forEach(([field, rules]) => {
        const ruleList = Array.isArray(rules) ? rules : [];

        for (const rule of ruleList) {
            const error = rule(values[field], values);
            if (error) {
                errors[field] = error;
                break;
            }
        }
    });

    return errors;
};

const clearFieldErrors = (form) => {
    form.querySelectorAll("[data-error-for]").forEach((el) => {
        el.textContent = "";
        el.classList.add("hidden");
    });

    form.querySelectorAll("[name]").forEach((el) => {
        el.classList.remove("border-red-400", "ring-2", "ring-red-100");
    });
};

const showFieldErrors = (form, errors) => {
    Object.entries(errors).forEach(([field, message]) => {
        const errorEl = form.querySelector(`[data-error-for="${field}"]`);
        const inputEl = form.querySelector(`[name="${field}"]`);

        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove("hidden");
        }

        if (inputEl) {
            inputEl.classList.add("border-red-400", "ring-2", "ring-red-100");
        }
    });
};

const normalizeApiErrors = (apiErrors = {}) => {
    const output = {};

    Object.entries(apiErrors).forEach(([key, messages]) => {
        output[key] = Array.isArray(messages) ? messages[0] : String(messages);
    });

    return output;
};

export {
    validators,
    validateValues,
    clearFieldErrors,
    showFieldErrors,
    normalizeApiErrors,
};
