package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.WebContext;

import com.treuominiemezzo.lido.dao.ContrattoDAO;
import com.treuominiemezzo.lido.model.Contratto;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.List;

@WebServlet("/contratti")
public class ContrattiServlet2 extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("text/html;charset=UTF-8");
    
    WebContext context = new WebContext(req, resp, getServletContext(), req.getLocale());

    int recordsPerPage = 50;
    int page = 1;
    String pageParam = req.getParameter("page");
    if (pageParam != null && !pageParam.isEmpty()) {
      try { page = Integer.parseInt(pageParam); } catch (NumberFormatException ignored) {}
    }
    int offset = (page - 1) * recordsPerPage;

    String dataDa = req.getParameter("data_da");
    String dataA = req.getParameter("data_a");

    ContrattoDAO dao = new ContrattoDAO();
    List<Contratto> contratti = dao.getContrattiFiltered(dataDa, dataA, recordsPerPage, offset);
    int totalRecords = dao.getTotalContrattiFiltered(dataDa, dataA);
    int totalPages = (int) Math.ceil((double) totalRecords / recordsPerPage);

    context.setVariable("contratti", contratti);
    context.setVariable("page", page);
    context.setVariable("totalPages", totalPages);

    TemplateEngine engine = ThymeleafConfig2.getTemplateEngine(getServletContext());
    try {
      engine.process("contratti", context, resp.getWriter());
    } catch (Exception e) {
      e.printStackTrace();
    }
  }
}