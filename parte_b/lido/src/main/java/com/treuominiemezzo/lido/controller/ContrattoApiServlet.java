package com.treuominiemezzo.lido.controller;

import com.treuominiemezzo.lido.dao.ContrattoDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.time.LocalDate;

// Endpoint for updating contracts
@WebServlet("/api/contratti")
@MultipartConfig
public class ContrattoApiServlet extends HttpServlet {

  @Override
  protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    String action = req.getParameter("action");
    
    if ("update".equals(action)) {
      try {
        int idContratto = Integer.parseInt(req.getParameter("id_contratto"));
        int idOmbrellone = Integer.parseInt(req.getParameter("id_ombrellone"));
        LocalDate inizio = LocalDate.parse(req.getParameter("data_inizio"));
        LocalDate fine = LocalDate.parse(req.getParameter("data_fine"));
        double prezzo = Double.parseDouble(req.getParameter("prezzo_totale"));

        ContrattoDAO dao = new ContrattoDAO();
        boolean success = dao.updateContratto(idContratto, idOmbrellone, inizio, fine, prezzo);

        sendJsonResponse(resp, success, success ? "Contract updated." : "Overlap error.");
      } catch (Exception e) {
        sendJsonResponse(resp, false, "Invalid data.");
      }
    }
  }

  private void sendJsonResponse(HttpServletResponse resp, boolean success, String message) throws IOException {
    resp.setContentType("application/json");
    resp.setCharacterEncoding("UTF-8");
    PrintWriter out = resp.getWriter();
    out.print("{\"success\": " + success + ", \"message\": \"" + message + "\"}");
    out.flush();
  }
}