import streamlit as st
import os
import json
import pandas as pd
import projects_manager as pm
import geo_processor as gp

st.set_config = st.set_page_config(page_title="GeoLens", layout="wide")

PROJECTS_DIR = getattr(pm, 'PROJECTS_DIR', 'projects')
if not os.path.exists(PROJECTS_DIR):
    os.makedirs(PROJECTS_DIR)

st.title("AI Satellite Annotation Tool")

uploaded_file = st.file_uploader("Upload your 4-band .tif satellite image", type=["tif", "tiff"])

if "zoom_level" not in st.session_state:
    st.session_state.zoom_level = 100

if uploaded_file is not None:
    file_bytes = uploaded_file.read()
    
    st.write("### 🔍 Map Zoom Control")
    zoom_cols = st.columns([1, 1, 1, 12])
    with zoom_cols[0]:
        if st.button("➕", key="z_in"):
            st.session_state.zoom_level += 20
    with zoom_cols[1]:
        if st.button("➖", key="z_out"):
            st.session_state.zoom_level = max(20, st.session_state.zoom_level - 20)
    with zoom_cols[2]:
        if st.button("⛶", key="z_reset"):
            st.session_state.zoom_level = 100
    with zoom_cols[3]:
        st.caption(f"Current Zoom: {st.session_state.zoom_level}%")

    tab1, tab2 = st.tabs(["Standard RGB View", "NDVI Heatmap View"])
    try:
        rgb_bytes, ndvi_bytes, stats = gp.process_geotiff(file_bytes)
        
        with tab1:
            st.image(rgb_bytes, use_container_width=True)
        with tab2:
            st.image(ndvi_bytes, use_container_width=True)
            st.markdown("### Precision Agriculture Health Analytics")
            
            if stats:
                df_stats = pd.DataFrame(stats)
                st.table(df_stats)
                
                csv_data = df_stats.to_csv(index=False).encode('utf-8')
                st.download_button("📥 Export Analytics as CSV", csv_data, "crop_health_stats.csv", "text/csv")
    except Exception as e:
        st.error(f"Error displaying images: {e}")
else:
    st.info("💡 Please upload a satellite GeoTIFF file to display the map views and start working.")


st.sidebar.title("Projects Dashboard")

with st.sidebar.form("create_project_form"):
    st.write("**+ Create New Project**")
    new_proj_name = st.text_input("Project Name:")
    new_proj_desc = st.text_area("Description:")
    if st.form_submit_button("Create Project"):
        if new_proj_name.strip():
            if pm.create_project(new_proj_name, new_proj_desc):
                st.success(f"Project '{new_proj_name}' created!")
                st.rerun()
            else:
                st.error("Project already exists!")

st.sidebar.markdown("---")

try:
    available_projects = pm.list_projects()
except:
    available_projects = [{"name": f, "description": ""} for f in os.listdir(PROJECTS_DIR) if os.path.isdir(os.path.join(PROJECTS_DIR, f))]

project_names = [p["name"] if isinstance(p, dict) else p for p in available_projects]

if project_names:
    selected_name = st.sidebar.selectbox("Select Active Project:", project_names)
    
    selected_desc = ""
    if available_projects and isinstance(available_projects[0], dict):
        selected_desc = next((p["description"] for p in available_projects if p["name"] == selected_name), "")
    st.sidebar.info(f"**Active:** {selected_name}\n\n*Description:* {selected_desc}")
    
    st.sidebar.markdown("---")
    
    st.sidebar.write("### 🏷️ Classes Management")
    
    with st.sidebar.popover("➕ Create Class"):
        st.write("**Add New Class**")
        class_name = st.text_input("Class Name")
        class_color = st.color_picker("Class Color", "#FF00F7")
        
        if st.button("Add Class"):
            if class_name.strip():
                if pm.add_project_class(selected_name, class_name, class_color):
                    st.toast(f"Class '{class_name}' added!")
                    st.rerun()
                else:
                    st.error("Class already exists!")

    project_meta_path = os.path.join(PROJECTS_DIR, selected_name, "metadata.json")
    current_classes = []
    
    if os.path.exists(project_meta_path):
        try:
            with open(project_meta_path, "r", encoding="utf-8") as f:
                meta_data = json.load(f)
                current_classes = meta_data.get("classes", [])
        except:
            pass
            
    if current_classes:
        c_names = [c["name"] for c in current_classes]
        active_c = st.sidebar.selectbox("🎯 Select Active Class:", c_names)
        
        chosen_color = next((c["color"] for c in current_classes if c["name"] == active_c), "#FFFFFF")
        st.sidebar.markdown(f"Color: <span style='color:{chosen_color}; font-weight:bold;'>■</span> {chosen_color}", unsafe_allow_html=True)
        
        if st.sidebar.button("🗑️ Delete Selected Class", use_container_width=True):
            if pm.delete_project_class(selected_name, active_c):
                st.toast(f"Class '{active_c}' deleted.")
                st.rerun()
    else:
        st.sidebar.caption("No classes created yet inside this project.")
        
    st.sidebar.markdown("---")
    if st.sidebar.button("🗑️ Delete Current Project", use_container_width=True, type="primary"):
        import shutil
        proj_full_path = os.path.join(PROJECTS_DIR, selected_name)
        if os.path.exists(proj_full_path):
            shutil.rmtree(proj_full_path)
            st.toast(f"Project '{selected_name}' deleted completely.")
            st.rerun()
else:
    st.sidebar.warning("No projects available yet. Create one first.")