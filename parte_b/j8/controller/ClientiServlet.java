package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.WebContext;
import org.thymeleaf.web.IWebExchange;
import org.thymeleaf.web.servlet.JakartaServletWebApplication;

import com.treuominiemezzo.lido.dao.ClienteDAO;
import com.treuominiemezzo.lido.model.Cliente;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.List;

// Questa Servlet risponderà all'URL /clienti
@WebServlet("/clienti")
public class ClientiServlet extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("text/html;charset=UTF-8");
    JakartaServletWebApplication application = JakartaServletWebApplication.buildApplication(getServletContext());
    IWebExchange exchange = application.buildExchange(req, resp);
    WebContext context = new WebContext(exchange, req.getLocale());

    int recordsPerPage = 50;
    int page = 1;
    String pageParam = req.getParameter("page");
    if (pageParam != null && !pageParam.isEmpty()) {
      try {
        page = Integer.parseInt(pageParam);
        if (page < 1)
          page = 1;
      } catch (NumberFormatException e) {
        page = 1;
      }
    }
    int offset = (page - 1) * recordsPerPage;

    String sortColumn = req.getParameter("sort");
    if (sortColumn == null)
      sortColumn = "codice";

    String sortDirection = req.getParameter("dir");
    if (sortDirection == null)
      sortDirection = "ASC";

    String searchNome = req.getParameter("search_nome");
    String searchCognome = req.getParameter("search_cognome");
    String annoNascita = req.getParameter("anno_nascita");
    String searchEmail = req.getParameter("search_email");
    String searchTelefono = req.getParameter("search_telefono");

    ClienteDAO dao = new ClienteDAO();
    List<Cliente> clienti = dao.getClientiFiltered(
        searchNome, searchCognome, annoNascita, searchEmail, searchTelefono,
        sortColumn, sortDirection, recordsPerPage, offset);

    int totalRecords = dao.getTotalClientiFiltered(searchNome, searchCognome, annoNascita, searchEmail, searchTelefono);
    int totalPages = (int) Math.ceil((double) totalRecords / recordsPerPage);

    context.setVariable("clienti", clienti);
    context.setVariable("page", page);
    context.setVariable("totalPages", totalPages);
    context.setVariable("sortColumn", sortColumn);
    context.setVariable("sortDirection", sortDirection);

    TemplateEngine engine = ThymeleafConfig.getTemplateEngine(getServletContext());
    try {
      engine.process("clienti", context, resp.getWriter());
    } catch (Exception e) {
      System.err.println("!!! THYMELEAF RENDERING ERROR !!!");
      e.printStackTrace();
    }
  }
}