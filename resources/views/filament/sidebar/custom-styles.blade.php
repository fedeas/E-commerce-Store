<style>
    [x-cloak] {
        display: none !important;
    }

    .fi-sidebar-nav-groups {
        display: none !important;
    }

    .fi-sidebar,
    .fi-sidebar-nav,
    .fi-sidebar-nav > div {
        padding-inline: 2px 0px !important;
        overflow: visible !important;
    }
    .cl-nav-root {
        position: relative;
        padding: 0.75rem 0.75rem 1rem 0.75rem;
    }

    .cl-sidebar-collapse-btn {
        width: 100%;
        min-height: 34px;
        margin-bottom: 0.65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #cad6e2;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #edf5fb 100%);
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.18s ease;
        white-space: nowrap;
    }

    .cl-sidebar-collapse-btn:hover {
        background: #f3f8ff;
        border-color: #9fb6c9;
        color: #0f172a;
    }

    .cl-nav-section-title {
        padding: 0 0.75rem 0.5rem 0.75rem;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: none;
        color: #94a3b8;
    }

    .cl-nav-card {
        border: 1px solid #dbe5ef;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        overflow: hidden;
    }

    .cl-nav-card-spaced {
        margin-top: 0.65rem;
    }

    .cl-nav-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 14px;
        border: 0;
        background: transparent;
        text-align: left;
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.18s ease;
    }

    .cl-nav-trigger:hover {
        background: #f3f8ff;
    }

    .cl-nav-trigger-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .cl-nav-folder {
        flex-shrink: 0;
        font-size: 16px;
        line-height: 1;
    }

    .cl-nav-trigger-label {
        white-space: nowrap;
    }

    .cl-nav-chevron {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #94a3b8;
        transition: transform 0.2s ease;
    }

    .cl-nav-list {
        padding: 0.5rem;
        border-top: 1px solid #e8eef5;
        display: grid;
        gap: 0.25rem;
        max-height: calc(100vh - 220px);
        overflow-y: auto;
        background: #ffffff;
    }

    .cl-nav-item,
    .cl-nav-flyout-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        color: #334155;
        text-decoration: none;
        transition: 0.18s ease;
    }

    .cl-nav-item:hover,
    .cl-nav-flyout-item:hover {
        background: #f4f8ff;
        color: #0f172a;
    }

    .cl-nav-item.is-active,
    .cl-nav-flyout-item.is-active {
        background: #e8f0ff;
        color: #0f5fd7;
        font-weight: 700;
    }

    .cl-nav-dot {
        width: 7px;
        height: 7px;
        margin-top: 7px;
        border-radius: 999px;
        background: #b6c2cf;
        flex-shrink: 0;
    }

    .cl-nav-item.is-active .cl-nav-dot,
    .cl-nav-flyout-item.is-active .cl-nav-dot {
        background: #0f5fd7;
    }

    .cl-nav-label {
        display: block;
        font-size: 14px;
        line-height: 1.35;
        word-break: break-word;
    }

    .cl-nav-flyout {
        position: fixed;
        top: 5.75rem;
        left: 6rem;
        right: 1rem;
        z-index: 80;
        border: 1px solid #cad6e2;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.16);
        overflow: hidden;
    }

    .cl-nav-flyout-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(180deg, #d7edf9 0%, #c3e3f4 100%);
        border-bottom: 1px solid #aac0d4;
    }

    .cl-nav-flyout-title {
        font-size: 15px;
        font-weight: 800;
        color: #14314b;
    }

    .cl-nav-flyout-close {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid #bfd0de;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        transition: 0.18s ease;
    }

    .cl-nav-flyout-close:hover {
        background: #f8fbff;
    }

    .cl-nav-flyout-body {
        padding: 16px;
        background: #ffffff;
    }

    .cl-nav-flyout-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 10px;
    }

    .cl-nav-flyout-item {
        min-height: 48px;
        border: 1px solid #e4ebf2;
        border-radius: 14px;
        background: #fbfdff;
    }

    .cl-nav-flyout-item:hover {
        border-color: #aac0d4;
    }

    @media (max-width: 1024px) {
        .cl-nav-flyout {
            left: 1rem;
            right: 1rem;
            top: 5rem;
        }
    }

    @media (max-width: 640px) {
        .cl-nav-flyout-grid {
            grid-template-columns: 1fr;
        }
    }
    .fi-main,
    .fi-main.fi-width-7xl,
    .fi-page,
    .fi-page-content,
    .fi-page-content-ctn,
    .fi-section-content,
    .fi-page-header-main-ctn,
    .fi-page-main {
        max-width: none !important;
        width: 100% !important;
    }

    .fi-main {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }

</style>
